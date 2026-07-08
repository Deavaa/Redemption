#!/usr/bin/env python3
"""
Replace all hardcoded emerald (#10b981) and cyan (#06b6d4) colors in the
news section (lines 455-810) of modern-glass.css with the school's
--primary-color / --primary-rgb so the news panel matches the navbar
and page-hero color scheme.
"""

css_path = '/home/z/my-project/Redemption/public/css/modern-glass.css'
with open(css_path, 'r') as f:
    lines = f.readlines()

# Only replace in the news section (section 4: NEWS SPLASH)
# Find the section boundaries
start_marker = "4. NEWS SPLASH"
end_marker = "5. ANNOUNCEMENT TICKER"

start_idx = None
end_idx = None
for i, line in enumerate(lines):
    if start_marker in line and start_idx is None:
        start_idx = i
    elif end_marker in line and start_idx is not None:
        end_idx = i
        break

if start_idx is None or end_idx is None:
    print(f"ERROR: Could not find section markers. start={start_idx}, end={end_idx}")
    exit(1)

print(f"Processing lines {start_idx+1} to {end_idx} (news section)")

replacements = 0
for i in range(start_idx, end_idx):
    original = lines[i]
    # Replace solid emerald -> primary
    lines[i] = lines[i].replace('#10b981', 'var(--primary-color)')
    # Replace solid cyan -> primary (use primary for both since we want single-color harmony)
    lines[i] = lines[i].replace('#06b6d4', 'var(--primary-color)')
    # Replace rgba(16, 185, 129, X) -> rgba(var(--primary-rgb), X)
    import re
    lines[i] = re.sub(r'rgba\(16,\s*185,\s*129,\s*([\d.]+)\)', r'rgba(var(--primary-rgb), \1)', lines[i])
    # Replace rgba(6, 182, 212, X) -> rgba(var(--primary-rgb), X)
    lines[i] = re.sub(r'rgba\(6,\s*182,\s*212,\s*([\d.]+)\)', r'rgba(var(--primary-rgb), \1)', lines[i])
    if lines[i] != original:
        replacements += 1

print(f"Replaced colors in {replacements} lines")

with open(css_path, 'w') as f:
    f.writelines(lines)

# Verify
content = ''.join(lines)
print(f"\nRemaining #10b981 in news section: {content[start_idx*1:end_idx*1].count('#10b981')}")
print(f"Remaining #06b6d4 in news section: {content[start_idx*1:end_idx*1].count('#06b6d4')}")
print(f"var(--primary-color) in news section: {content[start_idx*1:end_idx*1].count('var(--primary-color)')}")
print(f"var(--primary-rgb) in news section: {content[start_idx*1:end_idx*1].count('var(--primary-rgb)')}")
