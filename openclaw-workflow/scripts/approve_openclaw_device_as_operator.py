#!/usr/bin/env python3
import json
import os
import shutil
import subprocess
import tempfile
from pathlib import Path


base = Path("/home/openclaw/.openclaw")
paired_path = base / "devices/paired.json"
pending_path = base / "devices/pending.json"
auth_path = base / "identity/device-auth.json"
binary = "/home/openclaw/.local/bin/openclaw"

paired = json.loads(paired_path.read_text())
pending = json.loads(pending_path.read_text())

operator_token = next(
    device["tokens"]["operator"]["token"]
    for device in paired.values()
    if "operator" in device.get("tokens", {})
    and "operator.pairing" in device["tokens"]["operator"].get("scopes", [])
)

request = max(pending.values(), key=lambda item: item.get("ts", 0))
request_id = request["requestId"]
device_id = request["deviceId"]

temp_home = tempfile.mkdtemp(prefix="openclaw-operator-approval-")
try:
    result = subprocess.run(
        [
            binary,
            "devices",
            "approve",
            request_id,
            "--url",
            "ws://127.0.0.1:18789",
            "--token",
            operator_token,
            "--timeout",
            "30000",
            "--json",
        ],
        env={
            **os.environ,
            "HOME": temp_home,
            "PATH": "/home/openclaw/.local/bin:/usr/local/bin:/usr/bin:/bin",
        },
        check=False,
        capture_output=True,
        text=True,
    )
    print(result.stdout.strip())
    if result.returncode:
        print(result.stderr.strip())
        raise RuntimeError("OpenClaw operator approval command failed.")
finally:
    shutil.rmtree(temp_home, ignore_errors=True)

paired = json.loads(paired_path.read_text())
approved = paired[device_id]["tokens"].get("operator")
if not approved:
    raise RuntimeError("OpenClaw did not create an operator token for the approved server device.")

auth = json.loads(auth_path.read_text())
auth.setdefault("tokens", {})["operator"] = {
    "token": approved["token"],
    "role": approved["role"],
    "scopes": approved.get("scopes", []),
    "updatedAtMs": approved.get("createdAtMs"),
}
auth_path.write_text(json.dumps(auth, indent=2) + "\n")
os.chown(auth_path, 1000, 1000)
os.chmod(auth_path, 0o600)
print("Synchronized the approved operator token to the server CLI identity.")
