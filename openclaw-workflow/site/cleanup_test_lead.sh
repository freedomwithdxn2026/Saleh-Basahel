#!/usr/bin/env bash
set -euo pipefail

mysql salehbasahel <<'SQL'
SELECT COUNT(*) AS test_leads_before_delete FROM leads WHERE email = 'codex-test@example.com';
DELETE FROM leads WHERE email = 'codex-test@example.com';
SELECT COUNT(*) AS test_leads_after_delete FROM leads WHERE email = 'codex-test@example.com';
SQL

sudo -Hiu openclaw /home/openclaw/.local/bin/openclaw gateway status
