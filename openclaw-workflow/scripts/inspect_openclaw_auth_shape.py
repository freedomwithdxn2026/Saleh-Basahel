#!/usr/bin/env python3
import json
from pathlib import Path


def shape(value):
    if isinstance(value, dict):
        return {key: shape(item) for key, item in value.items()}
    if isinstance(value, list):
        return [shape(value[0])] if value else []
    return type(value).__name__


for path in [
    Path("/home/openclaw/.openclaw/devices/paired.json"),
    Path("/home/openclaw/.openclaw/identity/device-auth.json"),
]:
    print(path)
    print(json.dumps(shape(json.loads(path.read_text())), indent=2))
