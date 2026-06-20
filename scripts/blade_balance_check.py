#!/usr/bin/env python3
"""Simulate Blade's @if/@elseif/@else/@endif parsing to find imbalances."""
import re
import sys

def check_blade(path):
    with open(path, 'r', encoding='utf-8') as f:
        lines = f.readlines()

    depth = 0
    stack = []  # list of (line_num, directive, condition)

    for i, line in enumerate(lines, 1):
        # Find all Blade directives on this line
        # @if(...) @elseif(...) @else @endif @foreach(...) @endforeach etc.
        # But NOT inside {{-- --}} comments or <?php ?> tags
        # (Blade actually DOES parse inside comments — that was our earlier bug)

        # Skip lines that are purely <?php ?> raw PHP
        if line.strip().startswith('<?php') or line.strip().startswith('?>'):
            continue

        # Find @if, @elseif, @else, @endif, @foreach, @endforeach
        for match in re.finditer(r'@(if|elseif|else|endif|foreach|endforeach|forelse|empty|endforelse|php|endphp)\b', line):
            directive = match.group(1)
            col = match.start() + 1

            if directive == 'if':
                depth += 1
                # Extract condition
                cond = ''
                m = re.search(r'@if\s*\(([^)]*)\)', line[match.start():])
                if m:
                    cond = m.group(1)[:60]
                stack.append((i, 'if', cond))
            elif directive == 'elseif':
                if not stack or stack[-1][1] != 'if':
                    print(f"  L{i}: @{directive} without matching @if!")
                else:
                    pass  # elseif replaces the current branch
            elif directive == 'else':
                if not stack or stack[-1][1] != 'if':
                    print(f"  L{i}: @else without matching @if!")
            elif directive == 'endif':
                if not stack:
                    print(f"  L{i}: @endif with EMPTY stack (extra endif!)")
                    return i
                last = stack.pop()
                depth -= 1
            elif directive == 'foreach':
                depth += 1
                stack.append((i, 'foreach', ''))
            elif directive == 'endforeach':
                if not stack:
                    print(f"  L{i}: @endforeach with EMPTY stack")
                    return i
                last = stack.pop()
                depth -= 1
            elif directive == 'forelse':
                depth += 1
                stack.append((i, 'forelse', ''))
            elif directive == 'empty':
                pass
            elif directive == 'endforelse':
                if not stack:
                    print(f"  L{i}: @endforelse with EMPTY stack")
                    return i
                last = stack.pop()
                depth -= 1
            elif directive in ('php', 'endphp'):
                pass  # @php/@endphp are handled separately

    if stack:
        print(f"  UNCLOSED directives at end of file:")
        for ln, d, cond in stack:
            print(f"    L{ln}: @{d} ({cond})")
    else:
        print(f"  All directives balanced. Final depth: {depth}")

    return None

if __name__ == '__main__':
    for path in sys.argv[1:]:
        print(f"\n=== {path} ===")
        check_blade(path)
