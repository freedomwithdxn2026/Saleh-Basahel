import sys
from pathlib import Path

expected = sys.stdin.read().strip()
actual = ""

for line in Path("/var/www/salehbasahel/.env").read_text().splitlines():
    if line.startswith("ADMIN_PASSWORD="):
        actual = line.split("=", 1)[1].strip().strip('"').strip("'")
        break

print("match" if expected == actual else "mismatch", len(expected), len(actual))
