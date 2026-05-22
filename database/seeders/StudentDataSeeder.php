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
     * Seeds real students into the system, assigning them to the correct
     * grade/section based on their date of birth.
     *
     * Grade assignment (Academic Year 2025/2026):
     *   Grade 1  -> born 2018-2019  (age 6-7)
     *   Grade 2  -> born 2017-2018  (age 7-8)
     *   Grade 3  -> born 2016-2017  (age 8-9)
     *   Grade 4  -> born 2015-2016  (age 9-10)
     *   Grade 5  -> born 2014-2015  (age 10-11)
     *   Grade 6  -> born 2013-2014  (age 11-12)
     *   Grade 7  -> born 2012-2013  (age 12-13)
     *   Grade 8  -> born 2011-2012  (age 13-14)
     *   Grade 9  -> born 2010-2011  (age 14-15)
     *   Grade 10 -> born 2009-2010  (age 15-16)
     *   Grade 11 -> born 2008-2009  (age 16-17)
     *   Grade 12 -> born 2007-2008  (age 17-18)
     *
     * Students born outside 1995-2020 are skipped (out of school age range).
     */
    public function run(): void
    {
        $this->command->info('Seeding real student data...');

        // Resolve foreign-key dependencies
        $branch = Branch::where('is_headquarters', true)->first()
            ?? Branch::first();

        $ay = AcademicYear::where('is_current', true)->first()
            ?? AcademicYear::first();

        if (!$branch || !$ay) {
            $this->command->error('  No branch or academic year found. Run SchoolDataSeeder first.');
            return;
        }

        $this->command->info("  Using Branch: {$branch->name} (ID: {$branch->id})");
        $this->command->info("  Using AY: {$ay->name} (ID: {$ay->id})");

        // Build grade -> class/section maps (Grades 1-12)
        $allClasses = ClassRoom::where('branch_id', $branch->id)
            ->where('academic_year_id', $ay->id)
            ->get();

        $this->command->info("  Found {$allClasses->count()} classes in database");

        // Key by numeric_name, fallback to regex from name
        $classes = $allClasses->keyBy(function ($c) {
            if ($c->numeric_name) {
                return (int) $c->numeric_name;
            }
            if (preg_match('/(\d+)/', $c->name, $m)) {
                return (int) $m[1];
            }
            return 0;
        })->filter(fn($c, $k) => $k > 0);

        // Build sections per class
        $sectionsByClass = [];
        foreach ($classes as $gradeNum => $class) {
            $secs = Section::where('class_id', $class->id)
                ->orderBy('name')
                ->get()
                ->values();
            $sectionsByClass[$gradeNum] = $secs;
        }

        // Auto-create missing classes & sections for Grades 1-12
        for ($g = 1; $g <= 12; $g++) {
            if (!isset($classes[$g])) {
                $class = ClassRoom::updateOrCreate(
                    [
                        'branch_id'         => $branch->id,
                        'academic_year_id'  => $ay->id,
                        'numeric_name'      => $g,
                    ],
                    [
                        'name'     => 'Grade ' . $g,
                        'capacity' => 50,
                    ]
                );
                $classes[$g] = $class;
                $this->command->info("    Auto-created Grade {$g} (Class ID:{$class->id})");
            }

            if (!isset($sectionsByClass[$g]) || $sectionsByClass[$g]->isEmpty()) {
                $class = $classes[$g];
                $secs = Section::where('class_id', $class->id)
                    ->orderBy('name')
                    ->get()
                    ->values();

                if ($secs->isEmpty()) {
                    $secs = collect();
                    foreach (['A', 'B'] as $letter) {
                        $sec = Section::updateOrCreate(
                            [
                                'class_id' => $class->id,
                                'name'     => 'Section ' . $letter,
                            ],
                            [
                                'max_students' => 50,
                            ]
                        );
                        $secs->push($sec);
                    }
                    $this->command->info("    Auto-created sections for Grade {$g}: Section A, Section B");
                }

                $sectionsByClass[$g] = $secs;
            }
        }

        if ($classes->isEmpty()) {
            $this->command->error('  No classes found. Run SchoolDataSeeder first.');
            return;
        }

        // Delete previous seeder students (demo data or old batch)
        $demoCount = Student::whereNull('user_id')->count();
        if ($demoCount > 0) {
            Student::whereNull('user_id')->delete();
            $this->command->info("  Deleted {$demoCount} demo students (no user_id)");
        }
        $demoAdmissions = Student::where('admission_number', 'LIKE', 'SOR/2025/1%')->count();
        if ($demoAdmissions > 0) {
            Student::where('admission_number', 'LIKE', 'SOR/2025/1%')->delete();
            $this->command->info("  Deleted {$demoAdmissions} demo students (admission SOR/2025/1XXX)");
        }
        // Also clear previous batch from this seeder (SOR/2025/3XXX range)
        $prevBatch = Student::where('admission_number', 'LIKE', 'SOR/2025/3%')->count();
        if ($prevBatch > 0) {
            // Delete users linked to these students first
            $prevStudents = Student::where('admission_number', 'LIKE', 'SOR/2025/3%')->get();
            foreach ($prevStudents as $ps) {
                if ($ps->user_id) {
                    User::where('id', $ps->user_id)->delete();
                }
            }
            Student::where('admission_number', 'LIKE', 'SOR/2025/3%')->delete();
            $this->command->info("  Deleted {$prevBatch} previous batch students (SOR/2025/3XXX)");
        }

        // ── Student raw data ──────────────────────────────────────────
        // Format: [full_name, gender, date_of_birth]
        // Data fixes: 1010->2010, Feb 30->Feb 28, 1999-02-29->1999-02-28, duplicate removal
        $students = $this->getStudentData();

        $this->command->info("  Processing " . count($students) . " student records...");

        // ── Grade assignment logic ────────────────────────────────────
        // Initialize counters from existing roll_numbers to avoid duplicates
        $gradeCounters = [];
        foreach ($classes as $gradeNum => $class) {
            $gradeCounters[$gradeNum] = [0, 0];
            $sections = $sectionsByClass[$gradeNum] ?? collect();
            foreach ($sections as $sIdx => $sec) {
                $sectionLetter = $sIdx === 0 ? 'A' : chr(65 + $sIdx);
                $prefix = 'G' . $gradeNum . $sectionLetter;
                $maxExisting = Student::where('roll_number', 'LIKE', $prefix . '-%')
                    ->selectRaw("CAST(SUBSTRING(roll_number, -2) AS UNSIGNED) as rn")
                    ->orderByRaw('rn DESC')
                    ->first();
                if ($maxExisting && (int) $maxExisting->rn > $gradeCounters[$gradeNum][$sIdx]) {
                    $gradeCounters[$gradeNum][$sIdx] = (int) $maxExisting->rn;
                }
            }
        }

        // Find the max admission number to avoid collisions with existing records
        $maxAdmission = Student::where('admission_number', 'LIKE', 'SOR/2025/%')
            ->selectRaw("CAST(SUBSTRING(admission_number, -4) AS UNSIGNED) as num")
            ->orderByRaw('num DESC')
            ->first();
        $admissionNum = $maxAdmission ? ((int) $maxAdmission->num + 1) : 3001;

        $userCounter = 0;
        $created = 0;
        $skipped = 0;
        $errors = [];

        // Pre-cache the student role
        $studentRole = Role::where('name', 'student')->first();

        foreach ($students as $row) {
            $fullName = $row[0];
            $gender = $row[1];
            $dob = $row[2];
            $phone = $row[3] ?? null;

            try {
                // Calculate grade from DOB (academic year 2025/2026)
                $birthYear = (int) substr($dob, 0, 4);

                // Skip students with unrealistic DOBs
                if ($birthYear < 1995 || $birthYear > 2020) {
                    $this->command->warn("  Skipping {$fullName}: DOB {$dob} out of school age range");
                    $skipped++;
                    continue;
                }

                $gradeNum = max(1, min(12, 2026 - $birthYear - 6));

                // Find the class for this grade
                $class = $classes[$gradeNum] ?? null;
                if (!$class) {
                    $this->command->warn("  No class found for Grade {$gradeNum} - skipping {$fullName}");
                    $skipped++;
                    continue;
                }

                // Distribute between sections A/B
                if (!isset($gradeCounters[$gradeNum])) {
                    $gradeCounters[$gradeNum] = [0, 0];
                }

                $sectionIdx = ($gradeCounters[$gradeNum][0] <= $gradeCounters[$gradeNum][1]) ? 0 : 1;
                $section = $sectionsByClass[$gradeNum][$sectionIdx] ?? null;

                if (!$section) {
                    $section = $sectionsByClass[$gradeNum][0] ?? null;
                    $sectionIdx = 0;
                }

                if (!$section) {
                    $this->command->warn("  No section for Grade {$gradeNum} - skipping {$fullName}");
                    $skipped++;
                    continue;
                }

                $gradeCounters[$gradeNum][$sectionIdx]++;

                $sectionLetter = $sectionIdx === 0 ? 'A' : 'B';
                $rollNumber = 'G' . $gradeNum . $sectionLetter . '-' . str_pad($gradeCounters[$gradeNum][$sectionIdx], 2, '0', STR_PAD_LEFT);
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
        foreach ($gradeCounters as $gradeNum => $counts) {
            $total = $counts[0] + $counts[1];
            $this->command->info("    Grade {$gradeNum}: {$total} students (A:{$counts[0]} B:{$counts[1]})");
        }

        $this->command->newLine();
        $this->command->info('  Student login: email = <idNumber>@redemption.edu, password = DOB (e.g. 20020806)');
        $this->command->info('Student data seeded successfully!');
    }

    /**
     * Parse student data from SQL file or return hardcoded array.
     * Supports reading from database/seeders/data/students.sql
     * or using the hardcoded array below.
     *
     * Data fixes applied:
     *   - 1010-04-17 -> 2010-04-17 (typo)
     *   - 2002-02-30 -> 2002-02-28 (Feb 30 doesn't exist)
     *   - 2002-02-30 -> 2002-02-28 (same fix for second occurrence)
     *   - 1999-02-29 -> 1999-02-28 (1999 is not a leap year)
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
            // ... Add more or use the SQL file for the full list
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
