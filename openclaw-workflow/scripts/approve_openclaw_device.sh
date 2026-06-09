#!/usr/bin/env bash
set -euo pipefail

CONFIG=/home/openclaw/.openclaw/openclaw.json
OPENCLAW=/home/openclaw/.local/bin/openclaw
TOKEN="$(python3 - "$CONFIG" <<'PY'
import json
import sys

data = json.load(open(sys.argv[1]))
print(data["gateway"]["auth"]["token"])
PY
)"

for attempt in {1..12}; do
    if sudo -u openclaw env \
        HOME=/home/openclaw \
        PATH=/home/openclaw/.local/bin:/usr/local/bin:/usr/bin:/bin \
        "$OPENCLAW" devices approve --latest --token "$TOKEN" --timeout 30000 --json
    then
        exit 0
    fi

    echo "OpenClaw approval attempt $attempt failed; retrying..." >&2
    sleep 10
done

exit 1
