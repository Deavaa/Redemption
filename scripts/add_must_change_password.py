#!/usr/bin/env python3
"""
Add 'must_change_password' => true to all User::create() and User update
calls that set the password to the default '123456' or $defaultPassword.

This is a targeted edit: we look for patterns like:
  'password' => bcrypt('123456')
  'password' => Hash::make($defaultPassword)
  'password' => Hash::make('123456')
  'password' => $defaultPassword  (when $defaultPassword = '123456')
  'password' => bcrypt($defaultPassword)

And add 'must_change_password' => true to the same array (if not already present).
"""

import re

files = [
    '/home/z/my-project/Redemption/app/Http/Controllers/Teacher/TeacherController.php',
    '/home/z/my-project/Redemption/app/Http/Controllers/Student/StudentController.php',
    '/home/z/my-project/Redemption/app/Http/Controllers/Student/ParentController.php',
    '/home/z/my-project/Redemption/app/Http/Controllers/ParentModel/ParentModelController.php',
    '/home/z/my-project/Redemption/app/Http/Controllers/UserAccess/TeacherAccessController.php',
    '/home/z/my-project/Redemption/app/Http/Controllers/UserAccess/StudentAccessController.php',
    '/home/z/my-project/Redemption/app/Http/Controllers/UserAccess/ParentAccessController.php',
    '/home/z/my-project/Redemption/app/Http/Controllers/Admin/StaffController.php',
    '/home/z/my-project/Redemption/app/Http/Controllers/Admin/ProfileController.php',
    '/home/z/my-project/Redemption/app/Console/Commands/BackfillTeacherUsers.php',
    '/home/z/my-project/Redemption/database/seeders/DemoAdminSeeder.php',
]

# Patterns that indicate the default password is being used
# We look for lines that set 'password' => with a default-password expression
password_patterns = [
    r"'password'\s*=>\s*bcrypt\('123456'\)",
    r"'password'\s*=>\s*bcrypt\(\$defaultPassword\)",
    r"'password'\s*=>\s*Hash::make\('123456'\)",
    r"'password'\s*=>\s*Hash::make\(\$defaultPassword\)",
    r"'password'\s*=>\s*\$defaultPassword",
    r"'password'\s*=>\s*Hash::make\(\$validated\['password'\]\s*\?\?\s*\$defaultPassword\)",
]

total_changes = 0

for filepath in files:
    try:
        content = open(filepath, 'r').read()
    except FileNotFoundError:
        print(f'SKIP (not found): {filepath}')
        continue

    original = content
    changes_in_file = 0

    for pattern in password_patterns:
        # Find all matches
        for match in re.finditer(pattern, content):
            # Check if must_change_password is already present nearby (within 200 chars after)
            after_match = content[match.end():match.end()+300]
            if 'must_change_password' in after_match:
                continue  # Already has it, skip

            # Insert 'must_change_password' => true, after the password line
            # Find the end of the current line
            line_end = content.find('\n', match.end())
            if line_end == -1:
                line_end = match.end()

            # Get the indentation of the next line
            next_line_start = content.find(content[line_end+1:line_end+50].strip()[:1], line_end+1)
            if next_line_start == -1:
                continue

            # Extract indentation
            next_line = content[line_end+1:line_end+200].split('\n')[0]
            indent = next_line[:len(next_line) - len(next_line.lstrip())]

            # Insert the must_change_password line
            insertion = f"\n{indent}'must_change_password' => true,"
            content = content[:line_end] + insertion + content[line_end:]
            changes_in_file += 1

    if changes_in_file > 0:
        with open(filepath, 'w') as f:
            f.write(content)
        print(f'{filepath.split("/")[-1]}: {changes_in_file} insertion(s)')
        total_changes += changes_in_file
    else:
        print(f'{filepath.split("/")[-1]}: no changes (already has must_change_password or pattern not found)')

print(f'\nTotal insertions: {total_changes}')
