#!/usr/bin/env python3
"""Archive one OpenClaw WhatsApp session so it starts fresh next message."""

from __future__ import annotations

import json
import shutil
import time
from pathlib import Path


def main() -> int:
    root = Path.home() / ".openclaw" / "agents" / "main" / "sessions"
    store = root / "sessions.json"
    data = json.loads(store.read_text(encoding="utf-8"))

    sessions = data.get("sessions")
    entry = None

    entries: list[dict] = []

    if isinstance(data, dict):
        for key in list(data.keys()):
            if ":whatsapp:" in key:
                item = data.pop(key)
                if isinstance(item, dict):
                    entries.append(item)
                else:
                    entries.append({"key": key})
        entry = entries[0] if entries else None
    elif isinstance(sessions, dict):
        for key in list(sessions.keys()):
            if ":whatsapp:" in key:
                item = sessions.pop(key)
                if isinstance(item, dict):
                    entries.append(item)
                else:
                    entries.append({"key": key})
        entry = entries[0] if entries else None
    elif isinstance(sessions, list):
        kept = []
        for item in sessions:
            if isinstance(item, dict) and ":whatsapp:" in str(item.get("key", "")):
                entries.append(item)
            else:
                kept.append(item)
        data["sessions"] = kept
        entry = entries[0] if entries else None

    archive = root / ("archive-dxn-workflow-" + time.strftime("%Y%m%d-%H%M%S"))
    archive.mkdir(exist_ok=True)

    for entry in entries:
        if not entry.get("sessionId"):
            continue
        sid = entry["sessionId"]
        for path in root.glob(sid + "*"):
            shutil.move(str(path), archive / path.name)

    store.write_text(json.dumps(data, indent=2, sort_keys=True) + "\n", encoding="utf-8")
    print(f"Archived WhatsApp sessions: {len(entries)} -> {archive}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
