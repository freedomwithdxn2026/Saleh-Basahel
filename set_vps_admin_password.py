import sys
from pathlib import Path

env_path = Path("/var/www/salehbasahel/.env")
admin_password = sys.stdin.read().strip().lstrip("\ufeff")

if not admin_password:
    raise SystemExit("ADMIN_PASSWORD value was empty.")

lines = env_path.read_text().splitlines()
output = []
updated = False

for line in lines:
    if line.startswith("ADMIN_PASSWORD="):
        output.append(f'ADMIN_PASSWORD="{admin_password}"')
        updated = True
    else:
        output.append(line)

if not updated:
    output.append(f'ADMIN_PASSWORD="{admin_password}"')

env_path.write_text("\n".join(output) + "\n")
