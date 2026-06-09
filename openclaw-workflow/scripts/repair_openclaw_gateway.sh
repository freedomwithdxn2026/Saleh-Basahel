#!/usr/bin/env bash
set -euo pipefail

CONFIG=/home/openclaw/.openclaw/openclaw.json

python3 - "$CONFIG" <<'PY'
import json
import os
import pwd
import sys
from pathlib import Path

path = Path(sys.argv[1])
data = json.loads(path.read_text())
gateway = data.setdefault("gateway", {})
gateway.setdefault("remote", {})["token"] = gateway["auth"]["token"]
path.write_text(json.dumps(data, indent=2) + "\n")

user = pwd.getpwnam("openclaw")
os.chown(path, user.pw_uid, user.pw_gid)
os.chmod(path, 0o600)
PY

uid="$(id -u openclaw)"
sudo -u openclaw env XDG_RUNTIME_DIR="/run/user/$uid" systemctl --user restart openclaw-gateway.service
for _ in {1..45}; do
    if curl -fsS --max-time 2 http://127.0.0.1:18789/ >/dev/null 2>&1; then
        break
    fi
    sleep 2
done

TOKEN="$(python3 - "$CONFIG" <<'PY'
import json
import sys

data = json.load(open(sys.argv[1]))
print(data["gateway"]["auth"]["token"])
PY
)"

sudo -u openclaw env \
    HOME=/home/openclaw \
    PATH=/home/openclaw/.local/bin:/usr/local/bin:/usr/bin:/bin \
    /home/openclaw/.local/bin/openclaw devices approve --latest --token "$TOKEN" --json || true

sudo -u openclaw env \
    HOME=/home/openclaw \
    PATH=/home/openclaw/.local/bin:/usr/local/bin:/usr/bin:/bin \
    /home/openclaw/.local/bin/openclaw status --deep
