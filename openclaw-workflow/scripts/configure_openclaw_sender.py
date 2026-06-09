#!/usr/bin/env python3
import json
import os
import secrets
import shutil
import subprocess
import time
from pathlib import Path


uid = 1000
gid = 1000
main = Path("/home/openclaw/.openclaw")
sender_home = Path("/home/openclaw-sender")
sender = sender_home / ".openclaw"
binary = "/home/openclaw/.local/bin/openclaw"
timestamp = int(time.time() * 1000)

sender.mkdir(parents=True, exist_ok=True)
shutil.copy2(main / "openclaw.json", sender / "openclaw.json")
for shared_name in ["credentials", "extensions", "npm", "plugins"]:
    shared_path = sender / shared_name
    if shared_path.exists() or shared_path.is_symlink():
        if shared_path.is_symlink() or shared_path.is_file():
            shared_path.unlink()
        else:
            shutil.rmtree(shared_path)
    shared_path.symlink_to(main / shared_name, target_is_directory=True)
os.chown(sender_home, uid, gid)
os.chown(sender, uid, gid)
os.chown(sender / "openclaw.json", uid, gid)
os.chmod(sender / "openclaw.json", 0o600)

device_path = sender / "identity/device.json"
if not device_path.exists():
    subprocess.run(
        [
            "sudo",
            "-u",
            "openclaw",
            "env",
            f"HOME={sender_home}",
            "PATH=/home/openclaw/.local/bin:/usr/local/bin:/usr/bin:/bin",
            binary,
            "message",
            "send",
            "--channel=whatsapp",
            "--target=+971555574958",
            "--message=sender-bootstrap",
            "--json",
        ],
        check=False,
        capture_output=True,
        text=True,
        timeout=45,
    )

identity = json.loads(device_path.read_text())
sender_device_id = identity["deviceId"]

paired_path = main / "devices/paired.json"
pending_path = main / "devices/pending.json"
main_auth_path = main / "identity/device-auth.json"
sender_auth_path = sender / "identity/device-auth.json"

for path in [paired_path, pending_path, main_auth_path]:
    shutil.copy2(path, path.with_suffix(path.suffix + f".bak-sender-{timestamp}"))

paired = json.loads(paired_path.read_text())
pending = json.loads(pending_path.read_text())
scopes = ["operator.pairing", "operator.read", "operator.write"]
if sender_device_id not in paired:
    request = next(
        item for item in pending.values()
        if item.get("deviceId") == sender_device_id
    )
    token = secrets.token_urlsafe(32)
    paired[sender_device_id] = {
        "deviceId": sender_device_id,
        "publicKey": request["publicKey"],
        "displayName": "saleh-welcome-sender",
        "platform": request.get("platform", "linux"),
        "clientId": request.get("clientId", "cli"),
        "clientMode": request.get("clientMode", "cli"),
        "role": "operator",
        "roles": ["operator"],
        "scopes": scopes,
        "approvedScopes": scopes,
        "tokens": {
            "operator": {
                "token": token,
                "role": "operator",
                "scopes": scopes,
                "createdAtMs": timestamp,
            }
        },
        "createdAtMs": timestamp,
        "approvedAtMs": timestamp,
    }
else:
    sender_token = paired[sender_device_id]["tokens"]["operator"]
    token = sender_token["token"]
    scopes = sender_token.get("scopes", scopes)

main_auth = json.loads(main_auth_path.read_text())
main_device_id = main_auth["deviceId"]
if main_device_id in paired:
    main_device = paired[main_device_id]
    main_device["role"] = "node"
    main_device["roles"] = ["node"]
    main_device["scopes"] = []
    main_device["approvedScopes"] = []
    main_device["tokens"].pop("operator", None)
main_auth.get("tokens", {}).pop("operator", None)

pending = {
    request_id: item
    for request_id, item in pending.items()
    if item.get("deviceId") not in {sender_device_id, main_device_id}
}

sender_auth_path.parent.mkdir(parents=True, exist_ok=True)
sender_auth_path.write_text(json.dumps({
    "version": 1,
    "deviceId": sender_device_id,
    "tokens": {
        "operator": {
            "token": token,
            "role": "operator",
            "scopes": scopes,
            "updatedAtMs": timestamp,
        }
    },
}, indent=2) + "\n")

paired_path.write_text(json.dumps(paired, indent=2) + "\n")
pending_path.write_text(json.dumps(pending, indent=2) + "\n")
main_auth_path.write_text(json.dumps(main_auth, indent=2) + "\n")

for path in [paired_path, pending_path, main_auth_path, sender_auth_path]:
    os.chown(path, uid, gid)
    os.chmod(path, 0o600)

print("Configured a dedicated persistent OpenClaw sender identity.")
