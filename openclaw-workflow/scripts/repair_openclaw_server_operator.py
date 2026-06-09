#!/usr/bin/env python3
import json
import os
import secrets
import shutil
import time
from pathlib import Path


base = Path("/home/openclaw/.openclaw")
paired_path = base / "devices/paired.json"
pending_path = base / "devices/pending.json"
auth_path = base / "identity/device-auth.json"
timestamp = int(time.time() * 1000)

for path in [paired_path, pending_path, auth_path]:
    shutil.copy2(path, path.with_suffix(path.suffix + f".bak-{timestamp}"))

paired = json.loads(paired_path.read_text())
auth = json.loads(auth_path.read_text())
device_id = auth["deviceId"]
device = paired[device_id]
scopes = ["operator.pairing", "operator.read", "operator.write"]
token = secrets.token_urlsafe(32)

device["roles"] = sorted(set(device.get("roles", []) + ["operator"]))
device["role"] = "operator"
device["scopes"] = scopes
device["approvedScopes"] = scopes
device.setdefault("tokens", {})["operator"] = {
    "token": token,
    "role": "operator",
    "scopes": scopes,
    "createdAtMs": timestamp,
}
device["approvedAtMs"] = timestamp
paired_path.write_text(json.dumps(paired, indent=2) + "\n")

auth.setdefault("tokens", {})["operator"] = {
    "token": token,
    "role": "operator",
    "scopes": scopes,
    "updatedAtMs": timestamp,
}
auth_path.write_text(json.dumps(auth, indent=2) + "\n")

pending = json.loads(pending_path.read_text())
pending = {
    request_id: request
    for request_id, request in pending.items()
    if request.get("deviceId") != device_id
}
pending_path.write_text(json.dumps(pending, indent=2) + "\n")

for path in [paired_path, pending_path, auth_path]:
    os.chown(path, 1000, 1000)
    os.chmod(path, 0o600)

print("Repaired the server CLI operator authorization with minimum messaging scopes.")
