# DXN Lead Data

Runtime data lives on the Contabo VPS in:

- `/home/openclaw/.openclaw/workspace/data/leads.jsonl`
- `/home/openclaw/.openclaw/workspace/data/admin_alerts.jsonl`
- `/home/openclaw/.openclaw/workspace/data/meeting_reminders.jsonl`

Each line is one JSON object. Keep these files private.

Use `meeting_reminders.jsonl` when a lead has booked or requested a meeting reminder, but the current runtime cannot send the WhatsApp/email reminder automatically.
