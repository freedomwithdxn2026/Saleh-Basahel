#!/usr/bin/env python3
"""Append a DXN WhatsApp lead or admin alert as JSONL.

Usage:
  save_lead.py lead '{"name":"..."}'
  save_lead.py alert '{"question":"..."}'
  save_lead.py reminder '{"name":"...","meeting_time":"..."}'
"""

from __future__ import annotations

import json
import os
import sys
from datetime import datetime, timezone
from pathlib import Path
from urllib.error import HTTPError, URLError
from urllib.request import Request, urlopen


ROOT = Path.home() / ".openclaw" / "workspace"
DATA = ROOT / "data"
TARGETS = {
    "lead": DATA / "leads.jsonl",
    "alert": DATA / "admin_alerts.jsonl",
    "reminder": DATA / "meeting_reminders.jsonl",
}

DEFAULT_LEADS_WEBHOOK_URL = "https://salehbasahel.com/api/leads"
DEFAULT_TOKEN_FILE = ROOT / ".lead_webhook_token"


def stable_external_id(payload: dict) -> str:
    existing = (
        payload.get("external_id")
        or payload.get("whatsapp_user_id")
        or payload.get("thread_id")
        or payload.get("session_id")
    )

    if existing:
        return str(existing)

    phone = (
        payload.get("phone")
        or payload.get("whatsapp_number")
        or payload.get("number")
        or payload.get("from")
        or "unknown"
    )

    return f"whatsapp:{phone}"


def sync_laravel_leads(payload: dict) -> bool:
    webhook_url = os.environ.get("OPENCLAW_LEADS_WEBHOOK_URL", DEFAULT_LEADS_WEBHOOK_URL)
    if not webhook_url:
        return False

    lead_payload = dict(payload)
    lead_payload.setdefault("source", "whatsapp")
    lead_payload.setdefault("source_detail", "openclaw_whatsapp")
    lead_payload.setdefault("external_id", stable_external_id(lead_payload))
    lead_payload.setdefault("status", lead_payload.get("stage") or "new")

    token = os.environ.get("OPENCLAW_LEADS_WEBHOOK_TOKEN") or os.environ.get("LEAD_WEBHOOK_TOKEN")
    if not token and DEFAULT_TOKEN_FILE.is_file():
        token = DEFAULT_TOKEN_FILE.read_text(encoding="utf-8").strip()
    headers = {"Content-Type": "application/json"}
    if token:
        headers["Authorization"] = f"Bearer {token}"

    request = Request(
        webhook_url,
        data=json.dumps(lead_payload, ensure_ascii=False).encode("utf-8"),
        headers=headers,
        method="POST",
    )

    try:
        with urlopen(request, timeout=8) as response:
            response.read()
            return 200 <= response.status < 300
    except HTTPError as exc:
        print(f"Laravel lead sync failed: HTTP {exc.code}", file=sys.stderr)
    except URLError as exc:
        print(f"Laravel lead sync failed: {exc}", file=sys.stderr)

    return False


def sync_google_sheet(payload: dict) -> None:
    webhook_url = os.environ.get("OPENCLAW_GOOGLE_SHEETS_WEBHOOK_URL")
    if not webhook_url:
        return

    sheet_payload = {
        "secret": os.environ.get("OPENCLAW_GOOGLE_SHEETS_WEBHOOK_SECRET"),
        "date": payload.get("created_at"),
        "name": payload.get("name"),
        "whatsapp_number": payload.get("phone")
        or payload.get("whatsapp_number")
        or payload.get("number")
        or payload.get("from"),
        "email": payload.get("email"),
        "country": payload.get("country") or payload.get("city"),
        "interest_in": payload.get("interest") or payload.get("interest_in"),
        "meeting_date_time": payload.get("meeting_date_time") or payload.get("meeting_time"),
        "status": payload.get("meeting_status") or payload.get("stage") or "New",
        "notes": payload.get("notes") or payload.get("message"),
    }

    request = Request(
        webhook_url,
        data=json.dumps(sheet_payload, ensure_ascii=False).encode("utf-8"),
        headers={"Content-Type": "application/json"},
        method="POST",
    )

    try:
        with urlopen(request, timeout=8) as response:
            response.read()
    except URLError as exc:
        print(f"Google Sheet sync failed: {exc}", file=sys.stderr)


def main() -> int:
    if len(sys.argv) != 3 or sys.argv[1] not in TARGETS:
        print("Usage: save_lead.py lead|alert '<json-object>'", file=sys.stderr)
        return 2

    kind = sys.argv[1]
    try:
        payload = json.loads(sys.argv[2])
    except json.JSONDecodeError as exc:
        print(f"Invalid JSON: {exc}", file=sys.stderr)
        return 2

    if not isinstance(payload, dict):
        print("Payload must be a JSON object", file=sys.stderr)
        return 2

    payload.setdefault("created_at", datetime.now(timezone.utc).isoformat())
    payload.setdefault("source", "whatsapp")
    if kind == "lead":
        payload.setdefault("external_id", stable_external_id(payload))

    DATA.mkdir(parents=True, exist_ok=True)
    with TARGETS[kind].open("a", encoding="utf-8") as handle:
        handle.write(json.dumps(payload, ensure_ascii=False, sort_keys=True) + "\n")

    if kind == "lead":
        if not sync_laravel_leads(payload):
            sync_google_sheet(payload)

    print(str(TARGETS[kind]))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
