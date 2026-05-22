<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\User;
use App\Models\Branch;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Role;

class StudentDataSeeder extends Seeder
{
    /**
     * Run the student data seeder.
     *
     * Seeds real Lebu High School students into the system.
     * Lebu is a Secondary School (Grades 9-12).
     *
     * Grade assignment strategy:
     *   Since the source data does not include grade information, students are
     *   distributed across Grades 9-12 using a round-robin approach (sorted by
     *   DOB, youngest first). This gives a roughly equal distribution which is
     *   more realistic than a DOB-based formula that would place 80%+ in one grade.
     *
     *   You can update grades later via the admin panel or by editing the SQL file.
     *
     * Students with obviously invalid DOBs (born before 1995 or after 2012) are
     * skipped. You can adjust these thresholds below.
     */
    public function run(): void
    {
        $this->command->info('Seeding Lebu High School student data...');

        // ══════════════════════════════════════════════════════════
        // 1. RESOLVE BRANCH & ACADEMIC YEAR
        // ══════════════════════════════════════════════════════════
        $branch = Branch::where('name', 'LIKE', '%Lebu%')->first();
        if (!$branch) {
            $branch = Branch::where('is_headquarters', true)->first()
                ?? Branch::first();
        }

        $ay = AcademicYear::where('is_current', true)->first()
            ?? AcademicYear::first();

        if (!$branch || !$ay) {
            $this->command->error('  No branch or academic year found. Run SchoolDataSeeder first.');
            return;
        }

        $this->command->info("  Using Branch: {$branch->name} (ID: {$branch->id})");
        $this->command->info("  Using AY: {$ay->name} (ID: {$ay->id})");

        // ══════════════════════════════════════════════════════════
        // 2. CLEANUP: Remove ALL previous seeder students & users
        // ══════════════════════════════════════════════════════════
        // Delete students with user accounts created by this seeder
        // (identified by admission number pattern SOR/2025/3XXX or
        // user id_number pattern STU-2026-XXXX)
        $this->cleanupPreviousRuns();

        // Also clean demo students from old SchoolDataSeeder versions
        $demoCount = Student::whereNull('user_id')->count();
        if ($demoCount > 0) {
            Student::whereNull('user_id')->delete();
            $this->command->info("  Deleted {$demoCount} demo students (no user_id)");
        }

        // Clean students with old roll number format (e.g. A-01, B-01)
        $oldRollCount = Student::where('roll_number', 'REGEXP', '^[A-Z]-[0-9]')->count();
        if ($oldRollCount > 0) {
            $oldStudents = Student::where('roll_number', 'REGEXP', '^[A-Z]-[0-9]')->get();
            foreach ($oldStudents as $os) {
                if ($os->user_id) User::where('id', $os->user_id)->delete();
            }
            Student::where('roll_number', 'REGEXP', '^[A-Z]-[0-9]')->delete();
            $this->command->info("  Deleted {$oldRollCount} students with old roll_number format");
        }

        // Clean any remaining SOR/2025/1XXX or SOR/2026/1XXX demo students
        foreach (['SOR/2025/1%', 'SOR/2026/1%'] as $pattern) {
            $count = Student::where('admission_number', 'LIKE', $pattern)->count();
            if ($count > 0) {
                Student::where('admission_number', 'LIKE', $pattern)->delete();
                $this->command->info("  Deleted {$count} demo students ({$pattern})");
            }
        }

        // ══════════════════════════════════════════════════════════
        // 3. BUILD CLASS & SECTION MAPS (Grades 9-12 for Lebu)
        // ══════════════════════════════════════════════════════════
        $allClasses = ClassRoom::where('branch_id', $branch->id)
            ->where('academic_year_id', $ay->id)
            ->get();

        $this->command->info("  Found {$allClasses->count()} existing classes for Lebu branch");

        // Key classes by numeric_name
        $classes = $allClasses->keyBy(function ($c) {
            if ($c->numeric_name) return (int) $c->numeric_name;
            if (preg_match('/(\d+)/', $c->name, $m)) return (int) $m[1];
            return 0;
        })->filter(fn($c, $k) => $k >= 9 && $k <= 12);

        // Auto-create missing classes for Grades 9-12
        for ($g = 9; $g <= 12; $g++) {
            if (!isset($classes[$g])) {
                $class = ClassRoom::updateOrCreate(
                    [
                        'branch_id'        => $branch->id,
                        'academic_year_id' => $ay->id,
                        'numeric_name'     => $g,
                    ],
                    [
                        'name'     => 'Grade ' . $g,
                        'capacity' => 200,
                    ]
                );
                $classes[$g] = $class;
                $this->command->info("    Auto-created Grade {$g} (Class ID:{$class->id})");
            }
        }

        // Build sections per class — create enough sections for ~150+ students per grade
        $sectionsByClass = [];
        foreach ($classes as $gradeNum => $class) {
            $secs = Section::where('class_id', $class->id)
                ->orderBy('name')
                ->get()
                ->values();

            // Need at least 4 sections (A-D) per grade for ~150 students
            $minSections = 4;
            $sectionLetters = range('A', 'D'); // A, B, C, D
            if ($secs->count() < $minSections) {
                foreach ($sectionLetters as $letter) {
                    $sec = Section::updateOrCreate(
                        [
                            'class_id' => $class->id,
                            'name'     => 'Section ' . $letter,
                        ],
                        [
                            'max_students' => 50,
                        ]
                    );
                    // Add to collection if not already there
                    $existing = $secs->firstWhere('name', 'Section ' . $letter);
                    if (!$existing) {
                        $secs->push($sec);
                    }
                }
                $secs = Section::where('class_id', $class->id)
                    ->orderBy('name')
                    ->get()
                    ->values();
            }

            $sectionsByClass[$gradeNum] = $secs;
            $this->command->info("    Grade {$gradeNum}: " . $secs->count() . " sections");
        }

        // ══════════════════════════════════════════════════════════
        // 4. LOAD & FILTER STUDENT DATA
        // ══════════════════════════════════════════════════════════
        $students = $this->getStudentData();
        $this->command->info("  Loaded " . count($students) . " student records from data file");

        // Filter out students with invalid DOBs
        $validStudents = [];
        $invalidCount = 0;
        foreach ($students as $row) {
            $birthYear = (int) substr($row[2], 0, 4);
            // Skip students born before 1995 (too old) or after 2012 (too young for secondary)
            if ($birthYear < 1995 || $birthYear > 2012) {
                $this->command->warn("  Skipping {$row[0]}: DOB {$row[2]} (born {$birthYear}, outside 1995-2012 range)");
                $invalidCount++;
                continue;
            }
            $validStudents[] = $row;
        }

        if ($invalidCount > 0) {
            $this->command->info("  Filtered out {$invalidCount} students with invalid DOBs");
        }

        // Sort by DOB (youngest first) for round-robin distribution
        usort($validStudents, function ($a, $b) {
            return strcmp($a[2], $b[2]);
        });

        $totalValid = count($validStudents);
        $this->command->info("  Processing {$totalValid} valid student records...");

        // ══════════════════════════════════════════════════════════
        // 5. ROUND-ROBIN GRADE ASSIGNMENT
        // ══════════════════════════════════════════════════════════
        // Distribute students across G9-12 in round-robin fashion.
        // Since we don't have explicit grade data, this gives the most
        // realistic distribution for a secondary school.
        $gradeOrder = [9, 10, 11, 12];
        $gradeCounters = [];
        foreach ($gradeOrder as $g) {
            $gradeCounters[$g] = array_fill(0, $sectionsByClass[$g]->count(), 0);
        }

        // Initialize counters from existing roll_numbers to avoid duplicates
        foreach ($gradeOrder as $g) {
            $sections = $sectionsByClass[$g] ?? collect();
            foreach ($sections as $sIdx => $sec) {
                $sectionLetter = chr(65 + $sIdx);
                $prefix = 'G' . $g . $sectionLetter;
                $maxExisting = Student::where('roll_number', 'LIKE', $prefix . '-%')
                    ->selectRaw("CAST(SUBSTRING(roll_number, " . (strlen($prefix) + 2) . ") AS UNSIGNED) as rn")
                    ->orderByRaw('rn DESC')
                    ->first();
                if ($maxExisting && (int) $maxExisting->rn > $gradeCounters[$g][$sIdx]) {
                    $gradeCounters[$g][$sIdx] = (int) $maxExisting->rn;
                }
            }
        }

        // Find the max admission number to avoid collisions
        $maxAdmission = Student::where('admission_number', 'LIKE', 'SOR/2025/%')
            ->selectRaw("CAST(SUBSTRING(admission_number, -4) AS UNSIGNED) as num")
            ->orderByRaw('num DESC')
            ->first();
        $admissionNum = $maxAdmission ? ((int) $maxAdmission->num + 1) : 3001;

        // Also check SOR/2026/ pattern
        $maxAdmission2026 = Student::where('admission_number', 'LIKE', 'SOR/2026/%')
            ->selectRaw("CAST(SUBSTRING(admission_number, -4) AS UNSIGNED) as num")
            ->orderByRaw('num DESC')
            ->first();
        if ($maxAdmission2026) {
            $num2026 = (int) $maxAdmission2026->num + 1;
            $admissionNum = max($admissionNum, $num2026);
        }

        $userCounter = 0;
        $created = 0;
        $skipped = 0;
        $errors = [];

        // Pre-cache the student role
        $studentRole = Role::where('name', 'student')->first();

        // ══════════════════════════════════════════════════════════
        // 6. SEED STUDENTS
        // ══════════════════════════════════════════════════════════
        $gradeIdx = 0;
        $gradeCounts = [9 => 0, 10 => 0, 11 => 0, 12 => 0];

        foreach ($validStudents as $row) {
            $fullName = $row[0];
            $gender = $row[1];
            $dob = $row[2];
            $phone = $row[3] ?? null;

            try {
                // Round-robin grade assignment
                $gradeNum = $gradeOrder[$gradeIdx % count($gradeOrder)];
                $gradeIdx++;
                $gradeCounts[$gradeNum]++;

                // Find the class for this grade
                $class = $classes[$gradeNum] ?? null;
                if (!$class) {
                    $this->command->warn("  No class found for Grade {$gradeNum} - skipping {$fullName}");
                    $skipped++;
                    continue;
                }

                // Distribute between sections (balance across all sections)
                $numSections = $sectionsByClass[$gradeNum]->count();
                $sectionIdx = 0;
                $minCount = PHP_INT_MAX;
                for ($si = 0; $si < $numSections; $si++) {
                    if ($gradeCounters[$gradeNum][$si] < $minCount) {
                        $minCount = $gradeCounters[$gradeNum][$si];
                        $sectionIdx = $si;
                    }
                }

                $section = $sectionsByClass[$gradeNum][$sectionIdx] ?? null;
                if (!$section) {
                    $this->command->warn("  No section for Grade {$gradeNum} - skipping {$fullName}");
                    $skipped++;
                    continue;
                }

                $gradeCounters[$gradeNum][$sectionIdx]++;

                $sectionLetter = chr(65 + $sectionIdx); // A=0, B=1, C=2, D=3
                $seqNum = $gradeCounters[$gradeNum][$sectionIdx];
                $rollNumber = 'G' . $gradeNum . $sectionLetter . '-' . str_pad($seqNum, 2, '0', STR_PAD_LEFT);
                $admission = 'SOR/2025/' . str_pad($admissionNum, 4, '0', STR_PAD_LEFT);
                $admissionNum++;
                $genderLower = strtolower($gender);

                // Guardian info from name parts
                $nameParts = explode(' ', $fullName);
                $lastName = count($nameParts) >= 2 ? $nameParts[count($nameParts) - 1] : $nameParts[0];
                $secondName = count($nameParts) >= 3 ? $nameParts[1] : $lastName;
                $guardianName = $secondName . ' ' . $lastName;
                $guardianPhone = $phone;

                // ── Create User account ──
                $idNumber = 'STU-' . date('Y') . '-' . str_pad($userCounter + 1, 4, '0', STR_PAD_LEFT);
                $email = $idNumber . '@redemption.edu';
                $defaultPassword = str_replace('-', '', $dob); // e.g. 20020806

                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name'       => $fullName,
                        'id_number'  => $idNumber,
                        'password'   => bcrypt($defaultPassword),
                        'role'       => 'student',
                        'gender'     => $genderLower,
                        'phone'      => $phone,
                        'branch_id'  => $branch->id,
                        'is_active'  => true,
                    ]
                );
                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();
                    $user->save();
                }
                $userCounter++;

                // Assign Spatie 'student' role
                if ($studentRole && !$user->roles()->where('role_id', $studentRole->id)->exists()) {
                    $user->roles()->attach($studentRole->id);
                }

                // ── Create Student record ──
                Student::updateOrCreate(
                    ['admission_number' => $admission],
                    [
                        'user_id'            => $user->id,
                        'full_name'          => $fullName,
                        'branch_id'          => $branch->id,
                        'class_id'           => $class->id,
                        'section_id'         => $section->id,
                        'academic_year_id'   => $ay->id,
                        'gender'             => $genderLower,
                        'phone'              => $phone,
                        'roll_number'        => $rollNumber,
                        'admission_date'     => '2025-09-01',
                        'date_of_birth'      => $dob,
                        'guardian_name'      => $guardianName,
                        'guardian_phone'     => $guardianPhone,
                        'status'             => 'active',
                    ]
                );

                $created++;

            } catch (\Exception $e) {
                $skipped++;
                $errors[] = $fullName . ': ' . $e->getMessage();
                $this->command->warn("  Error importing {$fullName}: " . $e->getMessage());
                continue;
            }
        }

        // ── Summary ──
        $this->command->newLine();
        $this->command->info("  Students created/updated: {$created}");
        $this->command->info("  User accounts created: {$userCounter}");
        if ($skipped) {
            $this->command->warn("  Students skipped/errored: {$skipped}");
        }
        if (!empty($errors)) {
            $this->command->newLine();
            $this->command->error('  Error details:');
            foreach ($errors as $i => $err) {
                $this->command->error('    ' . ($i + 1) . '. ' . $err);
            }
        }

        $this->command->newLine();
        $this->command->info('  Grade distribution:');
        foreach ($gradeOrder as $g) {
            $counts = $gradeCounters[$g];
            $total = array_sum($counts);
            $detail = [];
            for ($i = 0; $i < count($counts); $i++) {
                $letter = chr(65 + $i);
                $detail[] = "{$letter}:{$counts[$i]}";
            }
            $this->command->info("    Grade {$g}: {$total} students (" . implode(' ', $detail) . ")");
        }

        $this->command->newLine();
        $this->command->info('  Student login: email = <idNumber>@redemption.edu, password = DOB (e.g. 20020806)');
        $this->command->warn('  NOTE: Grades were assigned by round-robin (no grade data in source). Update via admin panel.');
        $this->command->info('Lebu High School student data seeded successfully!');
    }

    /**
     * Clean up previous seeder runs: delete students and their user accounts
     * created by this seeder.
     */
    private function cleanupPreviousRuns(): void
    {
        // Delete students created by previous runs of this seeder
        // These are identified by admission numbers SOR/2025/3XXX-9XXX
        $patterns = ['SOR/2025/3%', 'SOR/2025/4%', 'SOR/2025/5%', 'SOR/2025/6%', 'SOR/2025/7%', 'SOR/2025/8%', 'SOR/2025/9%'];
        $totalDeleted = 0;
        foreach ($patterns as $pattern) {
            $students = Student::where('admission_number', 'LIKE', $pattern)->get();
            foreach ($students as $s) {
                if ($s->user_id) {
                    User::where('id', $s->user_id)->delete();
                }
                $s->delete();
                $totalDeleted++;
            }
        }
        if ($totalDeleted > 0) {
            $this->command->info("  Deleted {$totalDeleted} previous seeder students + user accounts");
        }

        // Also delete student user accounts with STU-2026 pattern
        $stuUsers = User::where('id_number', 'LIKE', 'STU-2026-%')->get();
        foreach ($stuUsers as $u) {
            // Delete linked student record first
            Student::where('user_id', $u->id)->delete();
            $u->delete();
        }
        if ($stuUsers->count() > 0) {
            $this->command->info("  Deleted {$stuUsers->count()} student user accounts (STU-2026-XXXX)");
        }
    }

    /**
     * Parse student data from SQL file or return hardcoded array.
     * Supports reading from database/seeders/data/students.sql
     * or using the hardcoded array below.
     *
     * Data fixes applied:
     *   - 1010-04-17 -> 2010-04-17 (typo)
     *   - 2002-02-30 -> 2002-02-28 (Feb 30 doesn't exist)
     *   - 1999-02-29 -> 1999-02-28 (1999 is not a leap year)
     *   - 2022-04-18 -> skipped (toddler, not school age)
     *   - 2019-04-15 -> skipped (toddler, not school age)
     *   - 2021-04-27 -> skipped (toddler, not school age)
     *   - Duplicate entries removed (same name + DOB)
     */
    private function getStudentData(): array
    {
        // Try to load from SQL file first (easier to update)
        $sqlFile = database_path('seeders/data/students.sql');
        if (file_exists($sqlFile)) {
            return $this->parseSqlFile($sqlFile);
        }

        // Fallback: hardcoded data (subset - use SQL file for full list)
        return [
            ['Youliyan Kitaw Mulugeta', 'Female', '2002-08-06'],
            ['Melat Yimneshnesh Girma', 'Female', '2010-01-15'],
            ['Eldana Misganaw Hone', 'Female', '2003-11-07'],
            ['Kalid Mohammed Mohammednur', 'Male', '2002-04-14'],
            ['Edidya Kidmealem Tilahun', 'Female', '2003-03-13'],
        ];
    }

    /**
     * Parse INSERT statements from a SQL file into student data array.
     * Handles: duplicate removal, invalid date fixes.
     */
    private function parseSqlFile(string $path): array
    {
        $content = file_get_contents($path);
        $pattern = "/VALUES\\s*\\('([^']+)',\\s*'([^']+)',\\s*'([^']+)'\\)/";
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $students = [];
        $seen = [];

        foreach ($matches as $match) {
            $name = trim($match[1]);
            $gender = trim($match[2]);
            $dob = trim($match[3]);

            // Fix invalid dates
            $dob = $this->fixDate($dob);

            // Remove duplicates (same name + DOB, case-insensitive)
            $key = strtolower($name) . '|' . $dob;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $students[] = [$name, $gender, $dob];
        }

        return $students;
    }

    /**
     * Fix common date errors in the source data.
     */
    private function fixDate(string $dob): string
    {
        // Fix year typo: 1010 -> 2010
        if (str_starts_with($dob, '1010-')) {
            $dob = '2010-' . substr($dob, 5);
        }

        // Fix Feb 30 -> Feb 28 (February never has 30 days)
        if (preg_match('/^(\d{4})-02-30$/', $dob, $m)) {
            $dob = $m[1] . '-02-28';
        }

        // Fix Feb 29 in non-leap years
        if (preg_match('/^(\d{4})-02-29$/', $dob, $m)) {
            $year = (int) $m[1];
            if (($year % 4 !== 0) || ($year % 100 === 0 && $year % 400 !== 0)) {
                $dob = $year . '-02-28';
            }
        }

        return $dob;
    }
}
