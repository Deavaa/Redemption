<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\User;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Student;
use App\Models\GradeScale;
use App\Models\Role;
use App\Models\StudentEnrollment;
use Illuminate\Support\Facades\DB;

class SchoolDataSeeder extends Seeder
{
    /**
     * Seeds ONLY the essential school infrastructure and real student data.
     * NO sample/mock users, teachers, subjects, or fake students.
     *
     * What this seeder creates:
     *   - Branches (Lebu + Tuludimtu)
     *   - Academic Year 2025/2026 + 3 Terms
     *   - Classes G1-12 + Sections (Tuludimtu A-B, Lebu A-D)
     *   - Grade Scales (system defaults)
     *   - 121 Real Tuludimtu students (G1-8)
     *   - Student Enrollments
     *   - Admin role assignment
     *
     * What this seeder does NOT create (use the UI to add):
     *   - Teachers, principal, registrar, finance users
     *   - Subjects
     *   - Teacher assignments / homeroom assignments
     *   - Calendar events
     *   - Lebu students (G9-12)
     */
    public function run(): void
    {
        $this->command->info('Seeding School of Redemption data...');

        // ======================================================================
        // 1. BRANCHES
        // ======================================================================
        $lebuBranch = Branch::updateOrCreate(
            ['name' => 'Lebu Campus'],
            [
                'address' => 'Lebu, Addis Ababa, Ethiopia',
                'phone' => '0112345678',
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
                'phone' => '0113456789',
                'email' => 'tuludimtu@schoolofredemption.edu',
                'is_headquarters' => false,
                'is_active' => true,
                'order' => 2,
            ]
        );

        $this->command->info('  Branches: Lebu + Tuludimtu');

        // ======================================================================
        // 2. ACADEMIC YEAR
        // ======================================================================
        $ay = AcademicYear::updateOrCreate(
            ['name' => '2025/2026'],
            ['start_date' => '2025-09-01', 'end_date' => '2026-07-15', 'is_current' => true]
        );
        $this->command->info('  Academic Year: 2025/2026');

        // ======================================================================
        // 3. TERMS
        // ======================================================================
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

        // ======================================================================
        // 4. ADMIN ROLE ASSIGNMENT
        // ======================================================================
        $adminUser = User::where('email', 'admin@school.com')->first();
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminUser && $adminRole && !$adminUser->roles()->where('role_id', $adminRole->id)->exists()) {
            $adminUser->roles()->attach($adminRole->id);
        }
        $this->command->info('  Admin role assigned');

        // ======================================================================
        // 5. CLASSES
        //    Tuludimtu: G1-8 (Sections A & B)
        //    Lebu: G9-12 (Sections A-D)
        // ======================================================================
        $tuludimtuClasses = [];
        for ($g = 1; $g <= 8; $g++) {
            $tuludimtuClasses[$g] = ClassRoom::updateOrCreate(
                ['name' => 'Grade ' . $g, 'branch_id' => $tuludimtuBranch->id, 'academic_year_id' => $ay->id],
                ['numeric_name' => $g, 'teacher_id' => null, 'capacity' => 50]
            );
        }

        $lebuClasses = [];
        for ($g = 9; $g <= 12; $g++) {
            $lebuClasses[$g] = ClassRoom::updateOrCreate(
                ['name' => 'Grade ' . $g, 'branch_id' => $lebuBranch->id, 'academic_year_id' => $ay->id],
                ['numeric_name' => $g, 'teacher_id' => null, 'capacity' => 200]
            );
        }
        $this->command->info('  Classes: 12 (Tuludimtu G1-8 + Lebu G9-12)');

        // ======================================================================
        // 6. SECTIONS
        //    Tuludimtu: Sections A & B per grade (16 sections)
        //    Lebu: Sections A-D per grade (16 sections)
        // ======================================================================
        $allSections = [];
        $tuludimtuSectionLetters = ['A', 'B'];

        foreach ($tuludimtuClasses as $gradeNum => $class) {
            foreach ($tuludimtuSectionLetters as $letter) {
                $key = $gradeNum . '_' . $letter;
                $allSections[$key] = Section::updateOrCreate(
                    ['class_id' => $class->id, 'name' => 'Section ' . $letter],
                    ['max_students' => 40, 'teacher_id' => null]
                );
            }
        }

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
        $this->command->info('  Sections: 32 (Tuludimtu: 16xA-B, Lebu: 16xA-D)');

        // ======================================================================
        // 7. STUDENTS - Clean up ALL old students first, then create fresh
        // ======================================================================
        $this->command->info('  Cleaning up old student data...');
        $oldStudentCount = Student::count();
        if ($oldStudentCount > 0) {
            // Collect student user_ids before deleting students
            $studentUserIds = Student::whereNotNull('user_id')->pluck('user_id')->toArray();

            // Delete all students first (avoids FK issues)
            Student::query()->delete();

            // Delete student user accounts
            if (!empty($studentUserIds)) {
                User::whereIn('id', $studentUserIds)->delete();
            }

            // Also delete student users via role assignment (covers edge cases)
            $studentRole = Role::where('name', 'student')->first();
            if ($studentRole) {
                $roleStudentUserIds = DB::table('role_user')
                    ->where('role_id', $studentRole->id)
                    ->pluck('user_id')
                    ->toArray();
                if (!empty($roleStudentUserIds)) {
                    User::whereIn('id', $roleStudentUserIds)->delete();
                }
            }

            $this->command->info("  Deleted {$oldStudentCount} old students");
        }

        // --- 7a. TULUDIMTU STUDENTS (121 real students) ---
        $tuludimtuStudentData = [
            ['Helana Tesfaye Gebrekidan', 'Female', '2018-08-06', '0911302896'],
            ['Yohana Dawit Ayalew', 'Female', '2011-02-23', '0911998833'],
            ['Eliana Heikki Juhan', 'Female', '2011-02-23', '0912049898'],
            ['Ananiya Tariku Alemu', 'Male', '2011-06-21', '0913360628'],
            ['Amnon Getnet Shew', 'Male', '2017-06-18', '0913072510'],
            ['Yamenada Samson Abera', 'Male', '2019-02-25', '0935006950'],
            ['Meklit Fantahun Siyum', 'Female', '2019-09-15', '0966960870'],
            ['Milki Samson Tekalign', 'Male', '2019-09-15', '0910420404'],
            ['Beka Alazar Abusie', 'Male', '2019-10-12', '0911333386'],
            ['Shalom Mesay Ashagrachew', 'Male', '2019-12-10', '0963703923'],
            ['Amnen Gezachew Demeke', 'Female', '2019-01-12', '0944741063'],
            ['Leul Michael Arayaselasse', 'Male', '2019-01-31', '0913828293'],
            ['Yeab Mesay Tesfay', 'Male', '2019-12-06', '0922412249'],
            ['Hezer Hermon Tadesse', 'Male', '2018-06-24', '0976141780'],
            ['Retina Semere Mezgebe', 'Female', '2019-01-03', '0911260022'],
            ['Evan Amanuel Hagos', 'Female', '2019-02-07', '0911111111'],
            ['Mohammod Abdulkedir Hussen', 'Male', '2018-07-31', '0910173332'],
            ['Meba Demisse Tsige', 'Male', '2018-02-21', '0911560448'],
            ['Naol Getu Teresa', 'Male', '2019-08-22', '0912477041'],
            ['Leul Matiyos Beyene', 'Male', '2018-06-11', '0930130570'],
            ['Hanifa Mohammod Adem', 'Female', '2018-06-17', '0974179999'],
            ['Maya Addisu Abinet', 'Female', '2018-07-17', '0913912514'],
            ['Selman Seid Yesuf', 'Male', '2019-03-06', '0920101067'],
            ['Atinaf Shunke Kebede', 'Female', '2019-06-11', '0920487826'],
            ['Eldana Fikadu Tamrat', 'Female', '2018-12-13', '0947410742'],
            ['Rebira Merga Bifa', 'Male', '2018-02-13', '0911879469'],
            ['Naol Abiy', 'Male', '2018-01-18', '0911223344'],
            ['Shem Demissie Tsige', 'Male', '2017-07-23', '0911560448'],
            ['Mekdelawit Michael Getu', 'Female', '2017-12-11', '0910546947'],
            ['Younis Abdurazak Abdulaziz', 'Male', '2017-11-04', '0911901715'],
            ['Elshaday Samuel Paulos', 'Female', '2017-12-16', '0917954202'],
            ['Yotor Zinaw Abdu', 'Male', '2018-07-21', '0910691535'],
            ['Maranat Surafel Getachew', 'Female', '2017-11-21', '0947976200'],
            ['Kalkidan Dagim Desalegn', 'Female', '2017-03-02', '0911786353'],
            ['Eyosias Befikadu Driba', 'Male', '2018-01-09', '0913472453'],
            ['Zenaida Tadele Kebede', 'Female', '2018-10-23', '0913972376'],
            ['Kirubel Wubetu Abera', 'Male', '2018-01-31', '0912659836'],
            ['Surafel Wubetu Abera', 'Female', '2018-01-31', '0912659836'],
            ['Dagmawit Mulubirhan Zelalem', 'Female', '2017-04-20', '0912487288'],
            ['Danawit Mulugeta Tegegne', 'Female', '2018-05-24', '0962872529'],
            ['Hamza Walelign Mesfin', 'Male', '2016-09-18', '0933333333'],
            ['Markon Haylat Gebre', 'Male', '2017-04-06', '0912878782'],
            ['Amen Mandefro Beyene', 'Male', '2017-06-06', '0913638856'],
            ['Yusuf Achemyelew Hussen', 'Male', '2016-06-28', '0911154827'],
            ['Nahom Solomon Birhanu', 'Male', '2017-02-25', '0916821316'],
            ['Zetsat Fikir Debebe', 'Male', '2018-01-03', '0911665185'],
            ['Emnet Azene Azene', 'Female', '2017-05-02', '0911542769'],
            ['Abigel Gezahagn Guta', 'Female', '2016-11-24', '0931707027'],
            ['Daniel Mulugeta Tegegne', 'Male', '2016-05-16', '0911670386'],
            ['Krubel Semere Mezgebu', 'Male', '2018-08-20', '0911212187'],
            ['Tselote Tsegaye Zeleke', 'Female', '2015-01-27', '091133445555'],
            ['Niftalem Habte Birhanu', 'Male', '2015-08-14', '0965580481'],
            ['Absalat Hermen Tadesse', 'Female', '2015-12-19', '0976141780'],
            ['Natan Mekdem Alemayehu', 'Male', '2015-06-05', '0911400244'],
            ['Menar Abdulnasir Bedru', 'Female', '2015-08-27', '0966968653'],
            ['Ruhama Demisse Kebede', 'Female', '2015-01-13', '0910420404'],
            ['Edidia Yosef Sisay', 'Female', '2016-10-08', '0912229439'],
            ['Ruth Henok Getachew', 'Female', '2015-03-20', '0910025368'],
            ['Yerosen Amanuel Asegid', 'Female', '2016-02-03', '0913986639'],
            ['Natan Wolday Hailegiorgis', 'Male', '2015-11-21', '0964732326'],
            ['Eldana Wondimu Gezahegn', 'Female', '2015-02-03', '0942058239'],
            ['Bana Haftamu Zeferu', 'Male', '2015-10-01', '0930362177'],
            ['Bitaniya Cherenet Tsegaye', 'Female', '2016-03-02', '0911039804'],
            ['Milki Bikila Kumera', 'Male', '2016-03-15', '0942443368'],
            ['Musud AbdulKedir Hussen', 'Male', '2015-10-22', '0910173332'],
            ['Yohana Biniam Solomon', 'Female', '2015-02-19', '0933221144'],
            ['Christian Azene Azene', 'Male', '2016-04-11', '0992799098'],
            ['Esrom Asmerom Arefayne', 'Male', '2016-06-22', '0948296497'],
            ['Aklesiya Dereje Girma', 'Female', '2015-02-26', '0912647392'],
            ['Amen Girma Abera', 'Male', '2015-03-09', '0945451230'],
            ['Miracle Amanuel Shonte', 'Female', '2016-11-28', '0919837714'],
            ['Aser Cherenet Tsegaye', 'Male', '2014-06-13', '0911039804'],
            ['Nyakaka Ruach Deng', 'Female', '2009-02-09', '0993965241'],
            ['Bezawit Alemu Desalegn', 'Female', '2014-04-15', '0982765095'],
            ['Yasir Seid Yesuf', 'Male', '2014-04-28', '0920101067'],
            ['Gebrel Tian Libo', 'Male', '2012-03-10', '0912652778'],
            ['Emanda Michael Mesfin', 'Female', '2015-02-12', '0910126272'],
            ['Paulos Samuel Paulos', 'Male', '2016-08-07', '0917954202'],
            ['Abem Kitaw Tale', 'Male', '2015-10-30', '0911707482'],
            ['Yosef Abdurazak Abdulaziz', 'Male', '2012-09-07', '0911901715'],
            ['Sitiyana Solomon Bahru', 'Female', '2015-02-14', '0916821316'],
            ['Amen Fikre Debebe', 'Male', '2015-10-07', '0911605185'],
            ['Feven Kelelew Wondifraw', 'Female', '2014-06-17', '0911102072'],
            ['Hermela Robel Mekonin', 'Female', '2014-02-15', '0934501319'],
            ['Eyuel Befikadu Bekele', 'Male', '2014-02-12', '0910726944'],
            ['Amar Abdulnasir Bedru', 'Male', '2014-02-19', '0966968053'],
            ['Delina Tesfaye Tsegaye', 'Female', '2012-01-23', '0934273055'],
            ['Yohannes Tesfaye Tsegaye', 'Male', '2009-11-10', '0934273055'],
            ['Christian Biniam Solomon', 'Male', '2013-09-26', '0911179471'],
            ['Afomia Asfaw Mekonnen', 'Female', '2013-09-03', '0920142002'],
            ['Ermias Semere Mezgebe', 'Female', '2013-12-03', '0911260022'],
            ['Natanem Tegegn Gashaw', 'Male', '2013-08-31', '0920334283'],
            ['Ibrahim Mohammod Awol', 'Male', '2014-03-08', '0912483109'],
            ['Eliham Achamyelew Husen', 'Female', '2013-06-11', '0911154827'],
            ['Ruth Befikadu Bekele', 'Female', '2014-02-04', '0910726944'],
            ['Biruk Aklilu Sima', 'Male', '2013-08-31', '0912107085'],
            ['Mariana Amanuel Haile', 'Female', '2013-10-01', '0911057829'],
            ['Abenezer Abate Tesfaye', 'Male', '2014-04-04', '0922660646'],
            ['Samuel Wondiu Gezahegn', 'Male', '2012-01-31', '0942058239'],
            ['Yafet Henok Getachew', 'Male', '2012-03-01', '0911173474'],
            ['Bemnet Mamush Tola', 'Male', '2012-08-20', '0911011456'],
            ['Yusuf Seid Yusuf', 'Male', '2012-04-11', '0920101067'],
            ['Amanuel Eshetu Leka', 'Male', '2012-10-12', '0911395692'],
            ['Yegetafiker Abebe Tulu', 'Male', '2013-05-26', '0913654668'],
            ['Eyosiyas Tewodros Haile', 'Male', '2008-02-11', '0911178544'],
            ['Samrawit Yidnekew Teka', 'Female', '2013-10-04', '0963181513'],
            ['Abuzer Abdulqadir Husen', 'Male', '2012-07-04', '0910173332'],
            ['Sifenan Abebe Tulu', 'Female', '2013-05-27', '0911747065'],
            ['Absalat Aklilu Tesfaye', 'Female', '2013-03-22', '0911086170'],
            ['Natnael Mulubrhan Tareke', 'Male', '2008-10-31', '0932162516'],
            ['Firew Ermiyas Bekele', 'Male', '2009-05-10', '0962010662'],
            ['Hallelujah Habte Berhanu', 'Male', '2012-12-23', '0911261007'],
            ['Eden Samuel Paulos', 'Female', '2011-09-28', '0917301191'],
            ['Amen Habte Birhanu', 'Female', '2012-06-13', '0911261007'],
            ['Fikir Mandefro Beyene', 'Female', '2008-06-18', '0911645949'],
            ['Sarem Haylat Gebre', 'Male', '2011-06-17', '0933085608'],
            ['Gelila Asfaw Mekonnen', 'Female', '2011-06-17', '0911421882'],
            ['Yohannes Gezahagne Tegegne', 'Male', '2013-11-12', '0920746877'],
            ['Fikir Abiy', 'Female', '2015-10-13', '0912345678'],
            ['Nanati Chala Gure', 'Female', '2018-11-22', '0900000000'],
            ['Dagim Mesfin Beza', 'Male', '2013-02-02', '0946464646'],
        ];

        $tuludimtuCount = $this->seedTuludimtuStudents(
            $tuludimtuBranch, $tuludimtuClasses, $allSections, $ay, $tuludimtuStudentData
        );
        $this->command->info("  Tuludimtu students: {$tuludimtuCount} (G1-8)");

        // ======================================================================
        // 8. GRADE SCALES (system defaults)
        // ======================================================================
        GradeScale::seedDefaults();
        $this->command->info('  Grade Scales: seeded');

        // ======================================================================
        // 9. STUDENT ENROLLMENTS - Register all students for current AY
        // ======================================================================
        $this->command->info('  Creating student enrollments...');
        $enrollmentCount = 0;
        $students = Student::where('academic_year_id', $ay->id)->where('status', 'active')->get();
        foreach ($students as $stu) {
            StudentEnrollment::updateOrCreate(
                ['student_id' => $stu->id, 'academic_year_id' => $ay->id],
                [
                    'branch_id' => $stu->branch_id,
                    'class_id' => $stu->class_id,
                    'section_id' => $stu->section_id,
                    'roll_number' => $stu->roll_number,
                    'enrollment_date' => $stu->admission_date ?? '2025-09-01',
                    'status' => 'enrolled',
                    'enrollment_type' => 'new',
                    'registration_fee' => 500.00,
                    'registration_fee_paid' => 0,
                    'registration_fee_status' => 'unpaid',
                    'enrolled_by' => $adminUser->id ?? 1,
                ]
            );
            $enrollmentCount++;
        }
        $this->command->info("  Student Enrollments: {$enrollmentCount}");

        // ======================================================================
        // DONE
        // ======================================================================
        $this->command->newLine();
        $this->command->info('School data seeded successfully!');
        $this->command->newLine();
        $this->command->table(['Account', 'Email', 'Password', 'Role'], [
            ['Admin', 'admin@school.com', '123456', 'admin'],
        ]);
        $this->command->newLine();
        $this->command->warn('NOTE: No sample teachers, subjects, or Lebu students were seeded.');
        $this->command->warn('Use the admin UI to add teachers, subjects, and enroll Lebu students.');
    }

    // ======================================================================
    // TULUDIMTU STUDENT SEEDING (121 real students, G1-8)
    // ======================================================================
    private function seedTuludimtuStudents(
        Branch $branch,
        array $tuludimtuClasses,
        array &$allSections,
        AcademicYear $ay,
        array $students
    ): int {
        $this->command->info('  Seeding Tuludimtu students (121 real)...');
        $studentRole = Role::where('name', 'student')->first();
        $admissionNum = 3001;
        $userCounter = 0;
        $created = 0;
        $skipped = 0;
        $gradeCounters = [];

        foreach ($students as $row) {
            [$fullName, $gender, $dob, $phone] = $row;
            try {
                $birthYear = (int) substr($dob, 0, 4);
                $gradeNum = max(1, min(8, 2025 - $birthYear - 5));

                $class = $tuludimtuClasses[$gradeNum] ?? null;
                if (!$class) { $skipped++; continue; }

                if (!isset($gradeCounters[$gradeNum])) {
                    $gradeCounters[$gradeNum] = [0, 0]; // A, B counters
                }
                $sectionIdx = ($gradeCounters[$gradeNum][0] <= $gradeCounters[$gradeNum][1]) ? 0 : 1;
                $sectionLetter = $sectionIdx === 0 ? 'A' : 'B';
                $sectionKey = $gradeNum . '_' . $sectionLetter;
                $section = $allSections[$sectionKey] ?? null;
                if (!$section) { $skipped++; continue; }

                $gradeCounters[$gradeNum][$sectionIdx]++;
                $rollNumber = 'G' . $gradeNum . $sectionLetter . '-' . str_pad($gradeCounters[$gradeNum][$sectionIdx], 2, '0', STR_PAD_LEFT);
                $admission = 'SOR/2025/' . str_pad($admissionNum, 4, '0', STR_PAD_LEFT);
                $admissionNum++;
                $genderLower = strtolower($gender);

                $nameParts = explode(' ', $fullName);
                $lastName = count($nameParts) >= 2 ? $nameParts[count($nameParts) - 1] : $nameParts[0];
                $secondName = count($nameParts) >= 3 ? $nameParts[1] : $lastName;
                $guardianName = $secondName . ' ' . $lastName;

                $idNumber = 'STU-TUL-' . str_pad($userCounter + 1, 4, '0', STR_PAD_LEFT);
                $email = $idNumber . '@redemption.edu';
                $defaultPassword = str_replace('-', '', $dob);

                $user = User::updateOrCreate(
                    ['email' => $email],
                    ['name' => $fullName, 'id_number' => $idNumber, 'password' => bcrypt($defaultPassword), 'role' => 'student', 'gender' => $genderLower, 'phone' => $phone, 'branch_id' => $branch->id, 'is_active' => true]
                );
                if (!$user->email_verified_at) { $user->email_verified_at = now(); $user->save(); }
                $userCounter++;

                if ($studentRole && !$user->roles()->where('role_id', $studentRole->id)->exists()) {
                    $user->roles()->attach($studentRole->id);
                }

                Student::updateOrCreate(
                    ['roll_number' => $rollNumber],
                    [
                        'user_id' => $user->id, 'full_name' => $fullName,
                        'branch_id' => $branch->id, 'class_id' => $class->id,
                        'section_id' => $section->id, 'academic_year_id' => $ay->id,
                        'gender' => $genderLower, 'phone' => $phone,
                        'admission_number' => $admission, 'admission_date' => '2025-09-01',
                        'date_of_birth' => $dob, 'guardian_name' => $guardianName,
                        'guardian_phone' => $phone, 'status' => 'active',
                    ]
                );

                $created++;
            } catch (\Exception $e) {
                $skipped++;
                continue;
            }
        }

        if ($skipped > 0) { $this->command->warn("    Skipped: {$skipped}"); }
        return $created;
    }
}
