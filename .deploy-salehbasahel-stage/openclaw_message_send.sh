#!/usr/bin/env bash
set -euo pipefail

TARGET="${1:-}"
MESSAGE="${2:-}"

if [[ ! "$TARGET" =~ ^\+[0-9]{8,15}$ ]]; then
    echo "Invalid WhatsApp target." >&2
    exit 2
fi

if [[ -z "$MESSAGE" || ${#MESSAGE} -gt 4000 ]]; then
    echo "Invalid WhatsApp message." >&2
    exit 2
fi

exec env \
    HOME=/home/openclaw-sender \
    PATH=/home/openclaw/.local/bin:/usr/local/bin:/usr/bin:/bin \
    /home/openclaw/.local/bin/openclaw message send \
    --channel whatsapp \
    --target "$TARGET" \
    --message "$MESSAGE" \
    --json
