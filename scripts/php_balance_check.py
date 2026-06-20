#!/usr/bin/env python3
"""Quick PHP brace/paren balance checker (not a real linter, but catches
the most common breakage from a sed-style edit)."""
import sys
import re

def check(path: str) -> list:
    with open(path, 'r', encoding='utf-8') as f:
        src = f.read()

    # Strip line comments and block comments
    src = re.sub(r'//[^\n]*', '', src)
    src = re.sub(r'#[^\n]*', '', src)
    src = re.sub(r'/\*.*?\*/', '', src, flags=re.DOTALL)

    # Strip single-quoted and double-quoted strings (escape-aware enough for most files)
    out = []
    i = 0
    n = len(src)
    while i < n:
        c = src[i]
        if c == "'":
            i += 1
            while i < n and src[i] != "'":
                if src[i] == '\\' and i + 1 < n:
                    i += 2
                    continue
                i += 1
            i += 1
            continue
        if c == '"':
            i += 1
            while i < n and src[i] != '"':
                if src[i] == '\\' and i + 1 < n:
                    i += 2
                    continue
                i += 1
            i += 1
            continue
        out.append(c)
        i += 1
    cleaned = ''.join(out)

    issues = []
    for opener, closer, name in [('{', '}', 'brace'), ('(', ')', 'paren'), ('[', ']', 'bracket')]:
        # Naive balance check (does not handle nested closure scoping, but catches gross mistakes)
        depth = 0
        min_depth = 0
        for ch in cleaned:
            if ch == opener:
                depth += 1
            elif ch == closer:
                depth -= 1
                if depth < min_depth:
                    min_depth = depth
        if depth != 0:
            issues.append(f"{name}: imbalance of {depth} (open - close)")
        if min_depth < 0:
            issues.append(f"{name}: closed more than opened (extra closer somewhere)")
    return issues

if __name__ == '__main__':
    bad = False
    for path in sys.argv[1:]:
        problems = check(path)
        if problems:
            bad = True
            print(f"{path}:")
            for p in problems:
                print(f"  - {p}")
        else:
            print(f"{path}: OK")
    sys.exit(1 if bad else 0)
