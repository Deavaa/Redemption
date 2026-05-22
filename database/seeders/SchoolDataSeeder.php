<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Student;
use App\Models\TeacherAssignment;
use App\Models\CalendarEvent;
use App\Models\GradeScale;
use App\Models\Role;

class SchoolDataSeeder extends Seeder
{
    /**
     * Run the comprehensive school data seeder.
     * Seeds ALL data in one place: structure + real student data.
     *
     * Campuses:
     *   - Lebu Campus: Secondary School (Grades 9-12) — 640+ real students from SQL file
     *     Sections: A, B, C, D per grade (capacity 200/grade)
     *   - Tuludimtu Campus: Primary School (Grades 1-8) — Section A only per grade
     *
     * Lebu students are distributed across G9-12 using round-robin (sorted by DOB).
     * Update grades later via the admin panel when you have actual grade data.
     */
    public function run(): void
    {
        $this->command->info('Seeding School of Redemption data...');

        // ══════════════════════════════════════════════════════════
        // 1. BRANCHES
        // ══════════════════════════════════════════════════════════
        $lebuBranch = Branch::updateOrCreate(
            ['name' => 'Lebu Campus'],
            [
                'address' => 'Lebu, Addis Ababa, Ethiopia',
                'phone' => '+251 11 234 5678',
                'email' => 'lebu@schoolofredemption.edu',
                'is_headquarters' => true,
                'is_active' => true,
                'order' => 1,
            ]
        );

        $tuludimtuBranch = Branch::updateOrCreate(
            ['name' => 'Tuludimtu Campus'],
            [
                'address' => 'Tuludimtu, Addis Ababa, Ethiopia',
                'phone' => '+251 11 345 6789',
                'email' => 'tuludimtu@schoolofredemption.edu',
                'is_headquarters' => false,
                'is_active' => true,
                'order' => 2,
            ]
        );

        $this->command->info('  Branches created: Lebu Campus + Tuludimtu Campus');

        // ══════════════════════════════════════════════════════════
        // 2. ACADEMIC YEAR
        // ══════════════════════════════════════════════════════════
        $ay = AcademicYear::updateOrCreate(
            ['name' => '2025/2026'],
            [
                'start_date' => '2025-09-01',
                'end_date' => '2026-07-15',
                'is_current' => true,
            ]
        );
        $this->command->info('  Academic Year: 2025/2026');

        // ══════════════════════════════════════════════════════════
        // 3. TERMS
        // ══════════════════════════════════════════════════════════
        Term::updateOrCreate(
            ['academic_year_id' => $ay->id, 'name' => 'Term 1'],
            ['start_date' => '2025-09-01', 'end_date' => '2025-12-20', 'term_number' => 1, 'is_active' => true]
        );
        Term::updateOrCreate(
            ['academic_year_id' => $ay->id, 'name' => 'Term 2'],
            ['start_date' => '2026-01-15', 'end_date' => '2026-04-10', 'term_number' => 2, 'is_active' => false]
        );
        Term::updateOrCreate(
            ['academic_year_id' => $ay->id, 'name' => 'Term 3'],
            ['start_date' => '2026-04-20', 'end_date' => '2026-07-15', 'term_number' => 3, 'is_active' => false]
        );
        $this->command->info('  Terms: 3');

        // ══════════════════════════════════════════════════════════
        // 4. USERS
        // ══════════════════════════════════════════════════════════
        $principalUser = User::updateOrCreate(
            ['email' => 'principal@school.com'],
            [
                'name' => 'Abebe Kebede', 'password' => bcrypt('123456'),
                'role' => 'branch_principal', 'branch_id' => $lebuBranch->id,
                'is_active' => true, 'email_verified_at' => now(),
            ]
        );

        $teacherData = [
            ['name' => 'Dawit Haile', 'email' => 'dawit@school.com'],
            ['name' => 'Tigist Mengistu', 'email' => 'tigist@school.com'],
            ['name' => 'Yonas Alemu', 'email' => 'yonas@school.com'],
            ['name' => 'Sara Tadesse', 'email' => 'sara@school.com'],
            ['name' => 'Mulugeta Fikre', 'email' => 'mulugeta@school.com'],
            ['name' => 'Helen Girma', 'email' => 'helen@school.com'],
        ];
        $teacherUsers = [];
        foreach ($teacherData as $td) {
            $teacherUsers[] = User::updateOrCreate(
                ['email' => $td['email']],
                ['name' => $td['name'], 'password' => bcrypt('123456'), 'role' => 'teacher', 'is_active' => true, 'email_verified_at' => now()]
            );
        }

        User::updateOrCreate(
            ['email' => 'registrar@school.com'],
            ['name' => 'Kidist Worku', 'password' => bcrypt('123456'), 'role' => 'registrar', 'branch_id' => $lebuBranch->id, 'is_active' => true, 'email_verified_at' => now()]
        );
        User::updateOrCreate(
            ['email' => 'finance@school.com'],
            ['name' => 'Bereket Tadesse', 'password' => bcrypt('123456'), 'role' => 'finance', 'branch_id' => $lebuBranch->id, 'is_active' => true, 'email_verified_at' => now()]
        );

        // Assign Spatie roles
        $adminUser = User::where('email', 'admin@school.com')->first();
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminUser && $adminRole && !$adminUser->roles()->where('role_id', $adminRole->id)->exists()) {
            $adminUser->roles()->attach($adminRole->id);
        }
        $bpRole = Role::where('name', 'branch_principal')->first();
        if ($principalUser && $bpRole && !$principalUser->roles()->where('role_id', $bpRole->id)->exists()) {
            $principalUser->roles()->attach($bpRole->id);
        }
        $teacherRole = Role::where('name', 'teacher')->first();
        foreach ($teacherUsers as $tu) {
            if ($teacherRole && !$tu->roles()->where('role_id', $teacherRole->id)->exists()) {
                $tu->roles()->attach($teacherRole->id);
            }
        }
        $this->command->info('  Users: 1 admin + 1 principal + 6 teachers + 2 staff');

        // ══════════════════════════════════════════════════════════
        // 5. TEACHERS
        // ══════════════════════════════════════════════════════════
        $principalTeacher = Teacher::updateOrCreate(
            ['user_id' => $principalUser->id],
            ['full_name' => 'Abebe Kebede', 'email' => 'principal@school.com', 'phone' => '+251 91 100 0001', 'qualification' => 'MEd', 'department' => 'Administration', 'hire_date' => '2010-09-01', 'salary' => 25000.00, 'status' => 'active']
        );

        $teacherRecords = [];
        $teacherMeta = [
            ['qual' => 'BEd Mathematics', 'dept' => 'Mathematics', 'phone' => '+251 91 200 0001'],
            ['qual' => 'MSc Physics', 'dept' => 'Science', 'phone' => '+251 91 200 0002'],
            ['qual' => 'BA English', 'dept' => 'Languages', 'phone' => '+251 91 200 0003'],
            ['qual' => 'BSc Biology', 'dept' => 'Science', 'phone' => '+251 91 200 0004'],
            ['qual' => 'MEd Social Studies', 'dept' => 'Social Studies', 'phone' => '+251 91 200 0005'],
            ['qual' => 'BEd Amharic', 'dept' => 'Languages', 'phone' => '+251 91 200 0006'],
        ];
        foreach ($teacherUsers as $i => $tu) {
            $teacherRecords[] = Teacher::updateOrCreate(
                ['user_id' => $tu->id],
                ['full_name' => $tu->name, 'email' => $tu->email, 'phone' => $teacherMeta[$i]['phone'], 'qualification' => $teacherMeta[$i]['qual'], 'department' => $teacherMeta[$i]['dept'], 'hire_date' => '2018-09-01', 'salary' => 15000.00, 'status' => 'active']
            );
        }
        $lebuBranch->update(['principal_id' => $principalTeacher->id]);
        $this->command->info('  Teachers: 7 including principal');

        // ══════════════════════════════════════════════════════════
        // 6. SUBJECTS
        // ══════════════════════════════════════════════════════════
        $subjectsData = [
            ['name' => 'Mathematics', 'code' => 'MATH', 'type' => 'compulsory', 'priority' => 1],
            ['name' => 'English', 'code' => 'ENG', 'type' => 'compulsory', 'priority' => 2],
            ['name' => 'Amharic', 'code' => 'AMH', 'type' => 'compulsory', 'priority' => 3],
            ['name' => 'Physics', 'code' => 'PHY', 'type' => 'compulsory', 'priority' => 4],
            ['name' => 'Chemistry', 'code' => 'CHEM', 'type' => 'compulsory', 'priority' => 5],
            ['name' => 'Biology', 'code' => 'BIO', 'type' => 'compulsory', 'priority' => 6],
            ['name' => 'Social Studies', 'code' => 'SOC', 'type' => 'compulsory', 'priority' => 7],
            ['name' => 'Civics', 'code' => 'CIV', 'type' => 'compulsory', 'priority' => 8],
            ['name' => 'ICT', 'code' => 'ICT', 'type' => 'compulsory', 'priority' => 9],
            ['name' => 'Physical Education', 'code' => 'PE', 'type' => 'elective', 'priority' => 10],
            ['name' => 'Art', 'code' => 'ART', 'type' => 'elective', 'priority' => 11],
            ['name' => 'Music', 'code' => 'MUS', 'type' => 'elective', 'priority' => 12],
        ];
        $subjects = [];
        foreach ($subjectsData as $sd) {
            $subjects[$sd['code']] = Subject::updateOrCreate(
                ['code' => $sd['code']],
                ['name' => $sd['name'], 'type' => $sd['type'], 'priority' => $sd['priority'], 'is_active' => true]
            );
        }
        $this->command->info('  Subjects: 12');

        // ══════════════════════════════════════════════════════════
        // 7. CLASSES — Tuludimtu G1-8 + Lebu G9-12
        // ══════════════════════════════════════════════════════════

        // Tuludimtu Campus: Grades 1-8 (Primary School) — only Section A
        $tuludimtuClasses = [];
        for ($g = 1; $g <= 8; $g++) {
            $tuludimtuClasses[$g] = ClassRoom::updateOrCreate(
                ['name' => 'Grade ' . $g, 'branch_id' => $tuludimtuBranch->id, 'academic_year_id' => $ay->id],
                ['numeric_name' => $g, 'teacher_id' => null, 'capacity' => 50]
            );
        }

        // Lebu Campus: Grades 9-12 (Secondary School) — Sections A, B, C, D
        $lebuClasses = [];
        for ($g = 9; $g <= 12; $g++) {
            $lebuClasses[$g] = ClassRoom::updateOrCreate(
                ['name' => 'Grade ' . $g, 'branch_id' => $lebuBranch->id, 'academic_year_id' => $ay->id],
                ['numeric_name' => $g, 'teacher_id' => null, 'capacity' => 200]
            );
        }
        $this->command->info('  Classes: 12 (Tuludimtu G1-8 + Lebu G9-12)');

        // ══════════════════════════════════════════════════════════
        // 8. SECTIONS
        //   Tuludimtu: Only Section A per grade (8 sections total)
        //   Lebu: Sections A, B, C, D per grade (16 sections total)
        // ══════════════════════════════════════════════════════════
        $allSections = [];  // key = "gradeNum_letter" => Section

        // Tuludimtu sections: ONLY Section A for each grade
        foreach ($tuludimtuClasses as $gradeNum => $class) {
            $key = $gradeNum . '_A';
            $allSections[$key] = Section::updateOrCreate(
                ['class_id' => $class->id, 'name' => 'Section A'],
                ['max_students' => 50, 'teacher_id' => null]
            );
        }

        // Lebu sections: A, B, C, D for each grade (4 sections × 4 grades = 16 sections)
        $lebuSectionLetters = ['A', 'B', 'C', 'D'];
        foreach ($lebuClasses as $gradeNum => $class) {
            foreach ($lebuSectionLetters as $letter) {
                $key = $gradeNum . '_' . $letter;
                $allSections[$key] = Section::updateOrCreate(
                    ['class_id' => $class->id, 'name' => 'Section ' . $letter],
                    ['max_students' => 50, 'teacher_id' => null]
                );
            }
        }

        // Assign homeroom teachers to Lebu Campus (G9A-12A)
        $homeroomMap = [
            '9_A' => $teacherRecords[0],
            '10_A' => $teacherRecords[1],
            '11_A' => $teacherRecords[2],
            '12_A' => $teacherRecords[3],
        ];
        foreach ($homeroomMap as $secKey => $teacher) {
            if (isset($allSections[$secKey])) {
                $allSections[$secKey]->update(['teacher_id' => $teacher->id]);
            }
            $gradeNum = explode('_', $secKey)[0];
            if (isset($lebuClasses[$gradeNum])) {
                $lebuClasses[$gradeNum]->update(['teacher_id' => $teacher->id]);
            }
        }
        $this->command->info('  Sections: 24 total (Tuludimtu: 8 × A only, Lebu: 16 × A-D)');

        // ══════════════════════════════════════════════════════════
        // 9. STUDENTS — Real Lebu High School students (G9-12)
        // ══════════════════════════════════════════════════════════
        $lebuStudentCount = $this->seedLebuStudents($lebuBranch, $lebuClasses, $allSections, $ay);
        $this->command->info("  Students: Lebu {$lebuStudentCount} (G9-12), Tuludimtu 0 (G1-8 - add via admin panel)");

        // ══════════════════════════════════════════════════════════
        // 10. TEACHER ASSIGNMENTS (Lebu G9-12, all sections A-D)
        // ══════════════════════════════════════════════════════════
        $assignmentMap = [
            [0, 9, 'MATH'],  [0, 10, 'MATH'], [0, 11, 'MATH'], [0, 12, 'MATH'],
            [1, 9, 'PHY'],   [1, 10, 'PHY'],  [1, 11, 'PHY'],  [1, 12, 'PHY'],
            [1, 9, 'CHEM'],  [1, 10, 'CHEM'], [1, 11, 'CHEM'], [1, 12, 'CHEM'],
            [2, 9, 'ENG'],   [2, 10, 'ENG'],  [2, 11, 'ENG'],  [2, 12, 'ENG'],
            [3, 9, 'BIO'],   [3, 10, 'BIO'],  [3, 11, 'BIO'],  [3, 12, 'BIO'],
            [3, 9, 'SOC'],   [3, 10, 'SOC'],  [3, 11, 'SOC'],  [3, 12, 'SOC'],
            [4, 9, 'AMH'],   [4, 10, 'AMH'],  [4, 11, 'AMH'],  [4, 12, 'AMH'],
            [4, 9, 'CIV'],   [4, 10, 'CIV'],  [4, 11, 'CIV'],  [4, 12, 'CIV'],
            [5, 9, 'ICT'],   [5, 10, 'ICT'],  [5, 11, 'ICT'],  [5, 12, 'ICT'],
            [5, 9, 'PE'],    [5, 10, 'PE'],   [5, 11, 'PE'],   [5, 12, 'PE'],
        ];

        $assignCount = 0;
        foreach ($assignmentMap as $am) {
            $teacher = $teacherRecords[$am[0]];
            $gradeNum = $am[1];
            $subject = $subjects[$am[2]];
            if (!isset($lebuClasses[$gradeNum]) || !$subject) continue;

            // Assign to each Lebu section (A, B, C, D)
            foreach ($lebuSectionLetters as $letter) {
                $secKey = $gradeNum . '_' . $letter;
                if (!isset($allSections[$secKey])) continue;
                TeacherAssignment::updateOrCreate(
                    ['teacher_id' => $teacher->id, 'class_id' => $lebuClasses[$gradeNum]->id, 'section_id' => $allSections[$secKey]->id, 'subject_id' => $subject->id, 'academic_year_id' => $ay->id],
                    []
                );
                $assignCount++;
            }

            // Also add null-section assignment (grade-level)
            TeacherAssignment::updateOrCreate(
                ['teacher_id' => $teacher->id, 'class_id' => $lebuClasses[$gradeNum]->id, 'section_id' => null, 'subject_id' => $subject->id, 'academic_year_id' => $ay->id],
                []
            );
            $assignCount++;
        }
        $this->command->info("  Teacher Assignments: {$assignCount}");

        // ══════════════════════════════════════════════════════════
        // 11. CALENDAR EVENTS
        // ══════════════════════════════════════════════════════════
        $events = [
            ['title' => 'School Reopening', 'category' => 'event', 'start' => '2025-09-01', 'end' => '2025-09-01', 'announcement' => true, 'color' => '#10b981'],
            ['title' => 'Mid-Term Exam', 'category' => 'exam', 'start' => '2025-11-10', 'end' => '2025-11-21', 'announcement' => true, 'color' => '#ef4444'],
            ['title' => 'Parent-Teacher Conference', 'category' => 'meeting', 'start' => '2025-12-05', 'end' => '2025-12-05', 'announcement' => true, 'color' => '#f59e0b'],
            ['title' => 'Term 1 End', 'category' => 'event', 'start' => '2025-12-20', 'end' => '2025-12-20', 'announcement' => false, 'color' => '#6366f1'],
            ['title' => 'Christmas Break', 'category' => 'holiday', 'start' => '2025-12-21', 'end' => '2026-01-14', 'announcement' => true, 'color' => '#10b981'],
            ['title' => 'Term 2 Begins', 'category' => 'event', 'start' => '2026-01-15', 'end' => '2026-01-15', 'announcement' => false, 'color' => '#6366f1'],
            ['title' => 'Science Fair', 'category' => 'event', 'start' => '2026-02-20', 'end' => '2026-02-20', 'announcement' => true, 'color' => '#8b5cf6'],
            ['title' => 'Sports Day', 'category' => 'event', 'start' => '2026-03-15', 'end' => '2026-03-15', 'announcement' => true, 'color' => '#f59e0b'],
        ];
        foreach ($events as $ev) {
            CalendarEvent::updateOrCreate(
                ['title' => $ev['title'], 'start_date' => $ev['start']],
                ['category' => $ev['category'], 'color' => $ev['color'], 'end_date' => $ev['end'], 'is_all_day' => true, 'is_announcement' => $ev['announcement'], 'academic_year_id' => $ay->id, 'branch_id' => $lebuBranch->id, 'created_by' => $adminUser->id ?? 1]
            );
        }
        $this->command->info('  Calendar Events: 8');

        // ══════════════════════════════════════════════════════════
        // 12. GRADE SCALES
        // ══════════════════════════════════════════════════════════
        GradeScale::seedDefaults();
        $this->command->info('  Grade Scales: 11 (A+ through F)');

        // ══════════════════════════════════════════════════════════
        // DONE
        // ══════════════════════════════════════════════════════════
        $this->command->newLine();
        $this->command->info('School data seeded successfully!');
        $this->command->newLine();
        $this->command->table(['Account', 'Email', 'Password', 'Role'], [
            ['Admin', 'admin@school.com', '123456', 'admin'],
            ['Principal', 'principal@school.com', '123456', 'branch_principal'],
            ['Teacher (Dawit)', 'dawit@school.com', '123456', 'teacher'],
            ['Teacher (Tigist)', 'tigist@school.com', '123456', 'teacher'],
            ['Teacher (Yonas)', 'yonas@school.com', '123456', 'teacher'],
            ['Teacher (Sara)', 'sara@school.com', '123456', 'teacher'],
            ['Teacher (Mulugeta)', 'mulugeta@school.com', '123456', 'teacher'],
            ['Teacher (Helen)', 'helen@school.com', '123456', 'teacher'],
            ['Registrar', 'registrar@school.com', '123456', 'registrar'],
            ['Finance', 'finance@school.com', '123456', 'finance'],
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    // LEBU STUDENT SEEDING (from SQL file)
    // ════════════════════════════════════════════════════════════════
    /**
     * Seed real Lebu High School students from the SQL data file.
     * Students are distributed across G9-12 using round-robin (sorted by DOB).
     * Each grade has Sections A, B, C, D — students are balanced across all sections.
     *
     * @return int Number of students created
     */
    private function seedLebuStudents(Branch $branch, array $lebuClasses, array &$allSections, AcademicYear $ay): int
    {
        $this->command->newLine();
        $this->command->info('  Seeding Lebu High School students (G9-12)...');

        // ── Load student data ──
        $students = $this->loadStudentData();
        if (empty($students)) {
            $this->command->warn('  No student data found. Skipping student seeding.');
            return 0;
        }
        $this->command->info("  Loaded " . count($students) . " raw student records");

        // ── Filter out invalid DOBs (after fixDate has already corrected typos) ──
        $validStudents = [];
        $invalidCount = 0;
        foreach ($students as $row) {
            $birthYear = (int) substr($row[2], 0, 4);
            if ($birthYear < 1995 || $birthYear > 2015) {
                $this->command->warn("    Skipping {$row[0]}: DOB {$row[2]} (born {$birthYear}, outside 1995-2015)");
                $invalidCount++;
                continue;
            }
            $validStudents[] = $row;
        }
        if ($invalidCount > 0) {
            $this->command->info("  Filtered out {$invalidCount} students with invalid DOBs after correction");
        }

        // Sort by DOB (youngest first) for round-robin
        usort($validStudents, fn($a, $b) => strcmp($a[2], $b[2]));
        $totalValid = count($validStudents);
        $this->command->info("  Processing {$totalValid} valid student records...");

        // ── Build section maps for Lebu G9-12 ──
        $gradeOrder = [9, 10, 11, 12];
        $lebuSectionLetters = ['A', 'B', 'C', 'D'];
        $lebuSections = []; // gradeNum => [0 => Section A, 1 => Section B, 2 => Section C, 3 => Section D]

        foreach ($gradeOrder as $g) {
            $lebuSections[$g] = [];
            foreach ($lebuSectionLetters as $idx => $letter) {
                $key = $g . '_' . $letter;
                if (isset($allSections[$key])) {
                    $lebuSections[$g][$idx] = $allSections[$key];
                }
            }
        }

        // ── Initialize roll number counters from existing data ──
        $gradeCounters = [];
        foreach ($gradeOrder as $g) {
            $gradeCounters[$g] = array_fill(0, count($lebuSections[$g]), 0);
            foreach ($lebuSections[$g] as $sIdx => $sec) {
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

        // ── Find max admission number ──
        $admissionNum = 3001;
        foreach (['SOR/2025/%', 'SOR/2026/%'] as $pattern) {
            $maxAdm = Student::where('admission_number', 'LIKE', $pattern)
                ->selectRaw("CAST(SUBSTRING(admission_number, -4) AS UNSIGNED) as num")
                ->orderByRaw('num DESC')
                ->first();
            if ($maxAdm) {
                $admissionNum = max($admissionNum, (int) $maxAdm->num + 1);
            }
        }

        $studentRole = Role::where('name', 'student')->first();
        $userCounter = 0;
        $created = 0;
        $skipped = 0;
        $gradeIdx = 0;
        $gradeCounts = [9 => 0, 10 => 0, 11 => 0, 12 => 0];
        $sectionCounts = [
            9 => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0],
            10 => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0],
            11 => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0],
            12 => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0],
        ];

        // ── Seed students ──
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

                $class = $lebuClasses[$gradeNum] ?? null;
                if (!$class) {
                    $skipped++;
                    continue;
                }

                // Balance across sections A, B, C, D — pick the one with fewest students
                $numSections = count($lebuSections[$gradeNum]);
                $sectionIdx = 0;
                $minCount = PHP_INT_MAX;
                for ($si = 0; $si < $numSections; $si++) {
                    if ($gradeCounters[$gradeNum][$si] < $minCount) {
                        $minCount = $gradeCounters[$gradeNum][$si];
                        $sectionIdx = $si;
                    }
                }

                $section = $lebuSections[$gradeNum][$sectionIdx] ?? null;
                if (!$section) {
                    $skipped++;
                    continue;
                }

                $gradeCounters[$gradeNum][$sectionIdx]++;
                $sectionLetter = chr(65 + $sectionIdx); // A, B, C, or D
                $seqNum = $gradeCounters[$gradeNum][$sectionIdx];
                $rollNumber = 'G' . $gradeNum . $sectionLetter . '-' . str_pad($seqNum, 2, '0', STR_PAD_LEFT);
                $admission = 'SOR/2025/' . str_pad($admissionNum, 4, '0', STR_PAD_LEFT);
                $admissionNum++;
                $genderLower = strtolower($gender);

                // Guardian info
                $nameParts = explode(' ', $fullName);
                $lastName = count($nameParts) >= 2 ? $nameParts[count($nameParts) - 1] : $nameParts[0];
                $secondName = count($nameParts) >= 3 ? $nameParts[1] : $lastName;
                $guardianName = $secondName . ' ' . $lastName;
                $guardianPhone = $phone;

                // Create User account
                $idNumber = 'STU-' . date('Y') . '-' . str_pad($userCounter + 1, 4, '0', STR_PAD_LEFT);
                $email = $idNumber . '@redemption.edu';
                $defaultPassword = str_replace('-', '', $dob);

                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $fullName, 'id_number' => $idNumber,
                        'password' => bcrypt($defaultPassword), 'role' => 'student',
                        'gender' => $genderLower, 'phone' => $phone,
                        'branch_id' => $branch->id, 'is_active' => true,
                    ]
                );
                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();
                    $user->save();
                }
                $userCounter++;

                if ($studentRole && !$user->roles()->where('role_id', $studentRole->id)->exists()) {
                    $user->roles()->attach($studentRole->id);
                }

                // Create Student record
                Student::updateOrCreate(
                    ['admission_number' => $admission],
                    [
                        'user_id' => $user->id, 'full_name' => $fullName,
                        'branch_id' => $branch->id, 'class_id' => $class->id,
                        'section_id' => $section->id, 'academic_year_id' => $ay->id,
                        'gender' => $genderLower, 'phone' => $phone,
                        'roll_number' => $rollNumber, 'admission_date' => '2025-09-01',
                        'date_of_birth' => $dob, 'guardian_name' => $guardianName,
                        'guardian_phone' => $guardianPhone, 'status' => 'active',
                    ]
                );

                $created++;
                $sectionCounts[$gradeNum][$sectionLetter]++;

            } catch (\Exception $e) {
                $skipped++;
                $this->command->warn("    Error: {$fullName}: " . $e->getMessage());
                continue;
            }
        }

        // ── Summary ──
        $this->command->newLine();
        $this->command->info("    Lebu students created: {$created}");
        $this->command->info("    User accounts created: {$userCounter}");
        if ($skipped > 0) {
            $this->command->warn("    Skipped/errored: {$skipped}");
        }

        $this->command->info('    Grade distribution:');
        foreach ($gradeOrder as $g) {
            $parts = [];
            $total = 0;
            foreach ($lebuSectionLetters as $l) {
                $c = $sectionCounts[$g][$l];
                $parts[] = "{$l}:{$c}";
                $total += $c;
            }
            $this->command->info("      Grade {$g}: {$total} students (" . implode(' ', $parts) . ")");
        }

        $this->command->newLine();
        $this->command->info('    Student login: email = <idNumber>@redemption.edu, password = DOB (e.g. 20020806)');
        $this->command->warn('    NOTE: Grades assigned by round-robin. Update via admin panel when you have actual grade data.');

        return $created;
    }

    // ════════════════════════════════════════════════════════════════
    // DATA LOADING & PARSING HELPERS
    // ════════════════════════════════════════════════════════════════

    /**
     * Load student data from SQL file.
     * The SQL file is at database/seeders/data/students.sql
     */
    private function loadStudentData(): array
    {
        $sqlFile = database_path('seeders/data/students.sql');
        if (file_exists($sqlFile)) {
            return $this->parseSqlFile($sqlFile);
        }
        $this->command->warn('  No students.sql file found at database/seeders/data/');
        return [];
    }

    /**
     * Parse INSERT statements from SQL file.
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

            $dob = $this->fixDate($dob);

            // Remove duplicates (same name + DOB, case-insensitive)
            $key = strtolower($name) . '|' . $dob;
            if (isset($seen[$key])) continue;
            $seen[$key] = true;

            $students[] = [$name, $gender, $dob];
        }

        return $students;
    }

    /**
     * Fix common date errors in the source data.
     * These are known typos from the original student records.
     */
    private function fixDate(string $dob): string
    {
        // Fix year typo: 1010 -> 2010 (missing leading '2')
        if (str_starts_with($dob, '1010-')) {
            $dob = '2010-' . substr($dob, 5);
        }
        // Fix year typo: 2019 -> 2009 (9 instead of 0)
        if (str_starts_with($dob, '2019-')) {
            $dob = '2009-' . substr($dob, 5);
        }
        // Fix year typo: 2021 -> 2001 (2 instead of 0)
        if (str_starts_with($dob, '2021-')) {
            $dob = '2001-' . substr($dob, 5);
        }
        // Fix year typo: 2022 -> 2002 (2 instead of 0)
        if (str_starts_with($dob, '2022-')) {
            $dob = '2002-' . substr($dob, 5);
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
