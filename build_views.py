#!/usr/bin/env python3
from pathlib import Path
ROOT = Path(__file__).resolve().parent
V = ROOT / "resources" / "views"

def w(rel, content):
    path = V / rel
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(content, encoding="utf-8")
    print("wrote", rel)

