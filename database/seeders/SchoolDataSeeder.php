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
     * Seeds all core entities in the correct FK dependency order.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding School of Redemption demo data...');

        // ══════════════════════════════════════════════════════════
        // 1. BRANCHES
        // ══════════════════════════════════════════════════════════
        $mainBranch = Branch::updateOrCreate(
            ['name' => 'Main Campus'],
            [
                'address' => 'Bole Road, Addis Ababa, Ethiopia',
                'phone' => '+251 11 234 5678',
                'email' => 'main@schoolofredemption.edu',
                'is_headquarters' => true,
                'is_active' => true,
                'order' => 1,
            ]
        );

        $branch2 = Branch::updateOrCreate(
            ['name' => 'CQA Campus'],
            [
                'address' => 'CQA Sub City, Addis Ababa, Ethiopia',
                'phone' => '+251 11 345 6789',
                'email' => 'cqa@schoolofredemption.edu',
                'is_headquarters' => false,
                'is_active' => true,
                'order' => 2,
            ]
        );

        $this->command->info('  ✓ Branches (2)');

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

        $this->command->info('  ✓ Academic Year (2025/2026)');

        // ══════════════════════════════════════════════════════════
        // 3. TERMS
        // ══════════════════════════════════════════════════════════
        $term1 = Term::updateOrCreate(
            ['academic_year_id' => $ay->id, 'name' => 'Term 1'],
            [
                'start_date' => '2025-09-01',
                'end_date' => '2025-12-20',
                'term_number' => 1,
                'is_active' => true,
            ]
        );

        $term2 = Term::updateOrCreate(
            ['academic_year_id' => $ay->id, 'name' => 'Term 2'],
            [
                'start_date' => '2026-01-15',
                'end_date' => '2026-04-10',
                'term_number' => 2,
                'is_active' => false,
            ]
        );

        $term3 = Term::updateOrCreate(
            ['academic_year_id' => $ay->id, 'name' => 'Term 3'],
            [
                'start_date' => '2026-04-20',
                'end_date' => '2026-07-15',
                'term_number' => 3,
                'is_active' => false,
            ]
        );

        $this->command->info('  ✓ Terms (3)');

        // ══════════════════════════════════════════════════════════
        // 4. USERS (Admin already exists from DemoAdminSeeder)
        // ══════════════════════════════════════════════════════════

        // Branch Principal
        $principalUser = User::updateOrCreate(
            ['email' => 'principal@school.com'],
            [
                'name' => 'Abebe Kebede',
                'password' => bcrypt('123456'),
                'role' => 'branch_principal',
                'branch_id' => $mainBranch->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Teachers (6)
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
                [
                    'name' => $td['name'],
                    'password' => bcrypt('123456'),
                    'role' => 'teacher',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Staff
        $registrarUser = User::updateOrCreate(
            ['email' => 'registrar@school.com'],
            [
                'name' => 'Kidist Worku',
                'password' => bcrypt('123456'),
                'role' => 'registrar',
                'branch_id' => $mainBranch->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $financeUser = User::updateOrCreate(
            ['email' => 'finance@school.com'],
            [
                'name' => 'Bereket Tadesse',
                'password' => bcrypt('123456'),
                'role' => 'finance',
                'branch_id' => $mainBranch->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role to admin user
        $adminUser = User::where('email', 'admin@school.com')->first();
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminUser && $adminRole && !$adminUser->roles()->where('role_id', $adminRole->id)->exists()) {
            $adminUser->roles()->attach($adminRole->id);
        }

        // Assign branch_principal role
        $bpRole = Role::where('name', 'branch_principal')->first();
        if ($principalUser && $bpRole && !$principalUser->roles()->where('role_id', $bpRole->id)->exists()) {
            $principalUser->roles()->attach($bpRole->id);
        }

        // Assign teacher role to teachers
        $teacherRole = Role::where('name', 'teacher')->first();
        foreach ($teacherUsers as $tu) {
            if ($teacherRole && !$tu->roles()->where('role_id', $teacherRole->id)->exists()) {
                $tu->roles()->attach($teacherRole->id);
            }
        }

        $this->command->info('  ✓ Users (1 admin + 1 principal + 6 teachers + 2 staff)');

        // ══════════════════════════════════════════════════════════
        // 5. TEACHERS
        // ══════════════════════════════════════════════════════════
        $principalTeacher = Teacher::updateOrCreate(
            ['user_id' => $principalUser->id],
            [
                'full_name' => 'Abebe Kebede',
                'email' => 'principal@school.com',
                'phone' => '+251 91 100 0001',
                'qualification' => 'MEd',
                'department' => 'Administration',
                'hire_date' => '2010-09-01',
                'salary' => 25000.00,
                'status' => 'active',
            ]
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
                [
                    'full_name' => $tu->name,
                    'email' => $tu->email,
                    'phone' => $teacherMeta[$i]['phone'],
                    'qualification' => $teacherMeta[$i]['qual'],
                    'department' => $teacherMeta[$i]['dept'],
                    'hire_date' => '2018-09-01',
                    'salary' => 15000.00,
                    'status' => 'active',
                ]
            );
        }

        $this->command->info('  ✓ Teachers (7 including principal)');

        // Update branch principal_id
        $mainBranch->update(['principal_id' => $principalTeacher->id]);

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
                [
                    'name' => $sd['name'],
                    'type' => $sd['type'],
                    'priority' => $sd['priority'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('  ✓ Subjects (12)');

        // ══════════════════════════════════════════════════════════
        // 7. CLASSES
        // ══════════════════════════════════════════════════════════
        $classesData = [
            ['name' => 'Grade 1', 'numeric' => 1],
            ['name' => 'Grade 2', 'numeric' => 2],
            ['name' => 'Grade 3', 'numeric' => 3],
            ['name' => 'Grade 4', 'numeric' => 4],
            ['name' => 'Grade 5', 'numeric' => 5],
            ['name' => 'Grade 6', 'numeric' => 6],
            ['name' => 'Grade 7', 'numeric' => 7],
            ['name' => 'Grade 8', 'numeric' => 8],
        ];

        $classes = [];
        foreach ($classesData as $cd) {
            $classes[$cd['numeric']] = ClassRoom::updateOrCreate(
                ['name' => $cd['name'], 'branch_id' => $mainBranch->id, 'academic_year_id' => $ay->id],
                [
                    'numeric_name' => $cd['numeric'],
                    'teacher_id' => null, // Will set homeroom below
                    'capacity' => 40,
                ]
            );
        }

        $this->command->info('  ✓ Classes (8: Grade 1-8)');

        // ══════════════════════════════════════════════════════════
        // 8. SECTIONS
        // ══════════════════════════════════════════════════════════
        $sections = [];
        $sectionNames = ['A', 'B'];
        foreach ($classes as $gradeNum => $class) {
            foreach ($sectionNames as $secName) {
                $key = $gradeNum . '_' . $secName;
                $sections[$key] = Section::updateOrCreate(
                    ['class_id' => $class->id, 'name' => 'Section ' . $secName],
                    [
                        'max_students' => 40,
                        'teacher_id' => null, // Homeroom teacher set below
                    ]
                );
            }
        }

        // Assign homeroom teachers (Grade 1A-4A to first 4 teachers)
        $homeroomMap = [
            '1_A' => $teacherRecords[0],
            '2_A' => $teacherRecords[1],
            '3_A' => $teacherRecords[2],
            '4_A' => $teacherRecords[3],
            '5_A' => $teacherRecords[4],
            '6_A' => $teacherRecords[5],
        ];

        foreach ($homeroomMap as $secKey => $teacher) {
            if (isset($sections[$secKey])) {
                $sections[$secKey]->update(['teacher_id' => $teacher->id]);
                // Also set homeroom on the class for the first one
                $gradeNum = explode('_', $secKey)[0];
                if ($secKey === $gradeNum . '_A' && isset($classes[$gradeNum])) {
                    $classes[$gradeNum]->update(['teacher_id' => $teacher->user_id]);
                }
            }
        }

        $this->command->info('  ✓ Sections (16: 2 per grade)');

        // ══════════════════════════════════════════════════════════
        // 9. STUDENTS
        // ══════════════════════════════════════════════════════════
        $firstNamesMale = ['Abel', 'Binyam', 'Chalachew', 'Daniel', 'Ermias', 'Fisha', 'Girma', 'Haben', 'Isayas', 'Jemal', 'Kaleb', 'Lij', 'Mikiyas', 'Natnael', 'Olana', 'Petros', 'Rediet', 'Samuel', 'Tariku', 'Wondimu'];
        $firstNamesFemale = ['Aster', 'Bethelhem', 'Chaltu', 'Dinknesh', 'Eleni', 'Fikir', 'Genet', 'Hiwot', 'Ikram', 'Jamila', 'Kidist', 'Lidya', 'Meron', 'Nardos', 'Olga', 'Peniel', 'Rahel', 'Selam', 'Tigist', 'Wubitu'];
        $lastNames = ['Abebe', 'Bekele', 'Chekol', 'Dagne', 'Engida', 'Fikru', 'Gebre', 'Hailu', 'Ibrahim', 'Jemaneh', 'Kassa', 'Lema', 'Mekonnen', 'Nega', 'Oumer', 'Pankhurst', 'Reda', 'Sisay', 'Tadesse', 'Wolde'];

        $studentCount = 0;
        $admissionNum = 1001;

        // Populate: ~15 students per section for grades 1-6
        foreach (range(1, 6) as $gradeNum) {
            foreach ($sectionNames as $secName) {
                $secKey = $gradeNum . '_' . $secName;
                if (!isset($sections[$secKey])) continue;

                $section = $sections[$secKey];
                $class = $classes[$gradeNum];

                for ($i = 0; $i < 15; $i++) {
                    $isMale = ($i % 2 === 0);
                    $firstName = $isMale ? $firstNamesMale[$i] : $firstNamesFemale[$i];
                    $lastName = $lastNames[($gradeNum * 3 + $i) % count($lastNames)];
                    $fullName = $firstName . ' ' . $lastName;
                    $gender = $isMale ? 'male' : 'female';
                    $admission = 'SOR/' . date('Y') . '/' . str_pad($admissionNum, 4, '0', STR_PAD_LEFT);

                    Student::updateOrCreate(
                        ['admission_number' => $admission],
                        [
                            'full_name' => $fullName,
                            'branch_id' => $mainBranch->id,
                            'class_id' => $class->id,
                            'section_id' => $section->id,
                            'academic_year_id' => $ay->id,
                            'gender' => $gender,
                            'roll_number' => $secName . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                            'admission_date' => '2025-09-01',
                            'date_of_birth' => (2017 - $gradeNum) . '-' . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
                            'guardian_name' => $lastName . ' ' . $lastNames[($i + 5) % count($lastNames)],
                            'guardian_phone' => '+251 9' . rand(1, 9) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999),
                            'status' => 'active',
                        ]
                    );

                    $admissionNum++;
                    $studentCount++;
                }
            }
        }

        // Grades 7-8: ~10 students per section
        foreach (range(7, 8) as $gradeNum) {
            foreach ($sectionNames as $secName) {
                $secKey = $gradeNum . '_' . $secName;
                if (!isset($sections[$secKey])) continue;

                $section = $sections[$secKey];
                $class = $classes[$gradeNum];

                for ($i = 0; $i < 10; $i++) {
                    $isMale = ($i % 2 === 0);
                    $firstName = $isMale ? $firstNamesMale[($i + 5) % 20] : $firstNamesFemale[($i + 5) % 20];
                    $lastName = $lastNames[($gradeNum * 2 + $i) % count($lastNames)];
                    $fullName = $firstName . ' ' . $lastName;
                    $gender = $isMale ? 'male' : 'female';
                    $admission = 'SOR/' . date('Y') . '/' . str_pad($admissionNum, 4, '0', STR_PAD_LEFT);

                    Student::updateOrCreate(
                        ['admission_number' => $admission],
                        [
                            'full_name' => $fullName,
                            'branch_id' => $mainBranch->id,
                            'class_id' => $class->id,
                            'section_id' => $section->id,
                            'academic_year_id' => $ay->id,
                            'gender' => $gender,
                            'roll_number' => $secName . '-' . str_pad($i + 1, 2, '0', STR_PAD_LEFT),
                            'admission_date' => '2025-09-01',
                            'date_of_birth' => (2017 - $gradeNum) . '-' . str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT),
                            'guardian_name' => $lastName . ' ' . $lastNames[($i + 3) % count($lastNames)],
                            'guardian_phone' => '+251 9' . rand(1, 9) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999),
                            'status' => 'active',
                        ]
                    );

                    $admissionNum++;
                    $studentCount++;
                }
            }
        }

        $this->command->info("  ✓ Students ({$studentCount})");

        // ══════════════════════════════════════════════════════════
        // 10. TEACHER ASSIGNMENTS
        // ══════════════════════════════════════════════════════════
        // Map teachers to subjects + classes
        // Teacher 0 (Dawit) → Math for Grade 5-8
        // Teacher 1 (Tigist) → Physics for Grade 7-8, Chemistry for Grade 7-8
        // Teacher 2 (Yonas) → English for Grade 5-8
        // Teacher 3 (Sara) → Biology for Grade 7-8, Social Studies for Grade 5-6
        // Teacher 4 (Mulugeta) → Amharic for Grade 5-8, Civics for Grade 5-6
        // Teacher 5 (Helen) → ICT for Grade 5-8, PE for Grade 5-8

        $assignmentMap = [
            // [teacherIndex, gradeNum, sectionName (null=all), subjectCode]
            [0, 5, null, 'MATH'],
            [0, 6, null, 'MATH'],
            [0, 7, null, 'MATH'],
            [0, 8, null, 'MATH'],

            [1, 7, null, 'PHY'],
            [1, 8, null, 'PHY'],
            [1, 7, null, 'CHEM'],
            [1, 8, null, 'CHEM'],

            [2, 5, null, 'ENG'],
            [2, 6, null, 'ENG'],
            [2, 7, null, 'ENG'],
            [2, 8, null, 'ENG'],

            [3, 7, null, 'BIO'],
            [3, 8, null, 'BIO'],
            [3, 5, null, 'SOC'],
            [3, 6, null, 'SOC'],

            [4, 5, null, 'AMH'],
            [4, 6, null, 'AMH'],
            [4, 7, null, 'AMH'],
            [4, 8, null, 'AMH'],
            [4, 5, null, 'CIV'],
            [4, 6, null, 'CIV'],

            [5, 5, null, 'ICT'],
            [5, 6, null, 'ICT'],
            [5, 7, null, 'ICT'],
            [5, 8, null, 'ICT'],
            [5, 5, null, 'PE'],
            [5, 6, null, 'PE'],
        ];

        $assignCount = 0;
        foreach ($assignmentMap as $am) {
            $teacher = $teacherRecords[$am[0]];
            $gradeNum = $am[1];
            $sectionName = $am[2]; // null = assign to both sections
            $subject = $subjects[$am[3]];

            if (!isset($classes[$gradeNum]) || !$subject) continue;

            $targetSections = $sectionName
                ? [($gradeNum . '_' . $sectionName) => $sections[$gradeNum . '_' . $sectionName] ?? null]
                : array_filter($sections, function ($s, $k) use ($gradeNum) {
                    return strpos($k, $gradeNum . '_') === 0;
                }, ARRAY_FILTER_USE_BOTH);

            foreach ($targetSections as $sec) {
                if (!$sec) continue;
                TeacherAssignment::updateOrCreate(
                    [
                        'teacher_id' => $teacher->id,
                        'class_id' => $classes[$gradeNum]->id,
                        'section_id' => $sec->id,
                        'subject_id' => $subject->id,
                        'academic_year_id' => $ay->id,
                    ],
                    []
                );
                $assignCount++;
            }
        }

        // Also add null-section assignments for core subjects (so they show up for all sections)
        foreach ($assignmentMap as $am) {
            $teacher = $teacherRecords[$am[0]];
            $gradeNum = $am[1];
            $subject = $subjects[$am[3]];

            if (!isset($classes[$gradeNum]) || !$subject) continue;

            TeacherAssignment::updateOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'class_id' => $classes[$gradeNum]->id,
                    'section_id' => null,
                    'subject_id' => $subject->id,
                    'academic_year_id' => $ay->id,
                ],
                []
            );
            $assignCount++;
        }

        $this->command->info("  ✓ Teacher Assignments ({$assignCount})");

        // ══════════════════════════════════════════════════════════
        // 11. CALENDAR EVENTS & ANNOUNCEMENTS
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
                [
                    'category' => $ev['category'],
                    'color' => $ev['color'],
                    'end_date' => $ev['end'],
                    'is_all_day' => true,
                    'is_announcement' => $ev['announcement'],
                    'academic_year_id' => $ay->id,
                    'branch_id' => $mainBranch->id,
                    'created_by' => $adminUser->id ?? 1,
                ]
            );
        }

        $this->command->info('  ✓ Calendar Events & Announcements (8)');

        // ══════════════════════════════════════════════════════════
        // 12. GRADE SCALES
        // ══════════════════════════════════════════════════════════
        GradeScale::seedDefaults();

        $this->command->info('  ✓ Grade Scales (11: A+ through F)');

        // ══════════════════════════════════════════════════════════
        // DONE
        // ══════════════════════════════════════════════════════════
        $this->command->newLine();
        $this->command->info('🎉 School data seeded successfully!');
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
}
