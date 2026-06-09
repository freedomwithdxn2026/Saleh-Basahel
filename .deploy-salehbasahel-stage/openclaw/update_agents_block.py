#!/usr/bin/env python3
"""Replace the Saleh WhatsApp workflow block in OpenClaw AGENTS.md."""

from __future__ import annotations

from pathlib import Path


STARTS = (
    "## DXN WhatsApp Lead Workflow",
    "## Private WhatsApp Lead Workflow And DXN Routing",
    "## Saleh Basahel WhatsApp Lead Assistant",
)


def strip_old_blocks(text: str) -> str:
    lines = text.splitlines()
    output: list[str] = []
    index = 0

    while index < len(lines):
        if lines[index] in STARTS:
            index += 1
            while index < len(lines) and not lines[index].startswith("## "):
                index += 1
            continue

        output.append(lines[index])
        index += 1

    return "\n".join(output).rstrip() + "\n"


def main() -> int:
    workspace = Path.home() / ".openclaw" / "workspace"
    agents = workspace / "AGENTS.md"
    block = workspace / "AGENTS_APPEND_DXN.md"
    agents.write_text(strip_old_blocks(agents.read_text(encoding="utf-8")) + block.read_text(encoding="utf-8"), encoding="utf-8")
    print("Updated AGENTS.md workflow block")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
