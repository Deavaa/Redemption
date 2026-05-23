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
use Illuminate\Support\Facades\DB;

class SchoolDataSeeder extends Seeder
{
    /**
     * ONE unified seeder. ALL student data is in THIS file.
     * No other student seeders required.
     *
     * Campuses:
     *   - Tuludimtu Campus: Primary School (G1-8) - 121 real students, Sections A & B
     *   - Lebu Campus: Secondary School (G9-12) - sample students, Sections A-D
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
        // 4. USERS
        // ======================================================================
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
        $this->command->info('  Users: admin + principal + 6 teachers + registrar + finance');

        // ======================================================================
        // 5. TEACHERS
        // ======================================================================
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
        $this->command->info('  Teachers: 7');

        // ======================================================================
        // 6. SUBJECTS
        // ======================================================================
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

        // ======================================================================
        // 7. CLASSES
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
        // 8. SECTIONS
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

        $homeroomMap = [
            '1_A' => $teacherRecords[0],
            '2_A' => $teacherRecords[1],
            '3_A' => $teacherRecords[2],
            '4_A' => $teacherRecords[3],
            '5_A' => $teacherRecords[4],
            '6_A' => $teacherRecords[5],
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
            $classMap = ($gradeNum <= 8) ? $tuludimtuClasses : $lebuClasses;
            if (isset($classMap[$gradeNum])) {
                $classMap[$gradeNum]->update(['teacher_id' => $teacher->id]);
            }
        }
        $this->command->info('  Sections: 32 (Tuludimtu: 16xA-B, Lebu: 16xA-D)');

        // ======================================================================
        // 9. STUDENTS - Clean up ALL old students first, then create fresh
        // ======================================================================
        // Delete ALL existing students and their user accounts to prevent
        // any duplicate roll_number or admission_number conflicts.
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
                $roleStudentUserIds = DB::table('model_has_roles')
                    ->where('role_id', $studentRole->id)
                    ->pluck('model_id')
                    ->toArray();
                if (!empty($roleStudentUserIds)) {
                    User::whereIn('id', $roleStudentUserIds)->delete();
                }
            }

            $this->command->info("  Deleted {$oldStudentCount} old students");
        }

        // --- 9a. TULUDIMTU STUDENTS (121 real students) ---
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

        // --- 9b. LEBU STUDENTS (sample data for G9-12) ---
        $lebuCount = $this->seedLebuStudents(
            $lebuBranch, $lebuClasses, $allSections, $ay
        );
        $this->command->info("  Lebu students: {$lebuCount} (G9-12, sample)");

        // ======================================================================
        // 10. TEACHER ASSIGNMENTS
        // ======================================================================
        $assignmentMap = [
            [0, 5, 'MATH'], [0, 6, 'MATH'], [0, 7, 'MATH'], [0, 8, 'MATH'],
            [0, 9, 'MATH'], [0, 10, 'MATH'], [0, 11, 'MATH'], [0, 12, 'MATH'],
            [1, 7, 'PHY'], [1, 8, 'PHY'], [1, 9, 'PHY'], [1, 10, 'PHY'],
            [1, 7, 'CHEM'], [1, 8, 'CHEM'], [1, 9, 'CHEM'], [1, 10, 'CHEM'],
            [2, 5, 'ENG'], [2, 6, 'ENG'], [2, 7, 'ENG'], [2, 8, 'ENG'],
            [2, 9, 'ENG'], [2, 10, 'ENG'], [2, 11, 'ENG'], [2, 12, 'ENG'],
            [3, 7, 'BIO'], [3, 8, 'BIO'], [3, 5, 'SOC'], [3, 6, 'SOC'],
            [3, 11, 'BIO'], [3, 12, 'BIO'],
            [4, 5, 'AMH'], [4, 6, 'AMH'], [4, 7, 'AMH'], [4, 8, 'AMH'],
            [4, 5, 'CIV'], [4, 6, 'CIV'],
            [4, 11, 'CHEM'], [4, 12, 'CHEM'],
            [5, 5, 'ICT'], [5, 6, 'ICT'], [5, 7, 'ICT'], [5, 8, 'ICT'],
            [5, 5, 'PE'], [5, 6, 'PE'],
            [5, 9, 'ICT'], [5, 10, 'ICT'],
        ];

        $assignCount = 0;
        $allClasses = $tuludimtuClasses + $lebuClasses;
        foreach ($assignmentMap as $am) {
            $teacher = $teacherRecords[$am[0]];
            $gradeNum = $am[1];
            $subject = $subjects[$am[2]];
            if (!isset($allClasses[$gradeNum]) || !$subject) continue;

            $secLetters = ($gradeNum <= 8) ? $tuludimtuSectionLetters : $lebuSectionLetters;
            foreach ($secLetters as $letter) {
                $secKey = $gradeNum . '_' . $letter;
                if (!isset($allSections[$secKey])) continue;
                TeacherAssignment::updateOrCreate(
                    ['teacher_id' => $teacher->id, 'class_id' => $allClasses[$gradeNum]->id, 'section_id' => $allSections[$secKey]->id, 'subject_id' => $subject->id, 'academic_year_id' => $ay->id],
                    []
                );
                $assignCount++;
            }

            TeacherAssignment::updateOrCreate(
                ['teacher_id' => $teacher->id, 'class_id' => $allClasses[$gradeNum]->id, 'section_id' => null, 'subject_id' => $subject->id, 'academic_year_id' => $ay->id],
                []
            );
            $assignCount++;
        }
        $this->command->info("  Teacher Assignments: {$assignCount}");

        // ======================================================================
        // 11. CALENDAR EVENTS
        // ======================================================================
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

        // ======================================================================
        // 12. GRADE SCALES
        // ======================================================================
        GradeScale::seedDefaults();
        $this->command->info('  Grade Scales: 11');

        // ======================================================================
        // DONE
        // ======================================================================
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

    // ======================================================================
    // LEBU STUDENT SEEDING (sample data, G9-12, Sections A-D)
    // ======================================================================
    private function seedLebuStudents(
        Branch $branch,
        array $lebuClasses,
        array &$allSections,
        AcademicYear $ay
    ): int {
        $this->command->info('  Seeding Lebu students (sample G9-12)...');
        $maleFirst = ['Abel','Abenezer','Amanuel','Bereket','Biruk','Dagim','Dawit','Elias','Ermias','Esrom','Eyuel','Henok','Kaleb','Leul','Michael','Mikiyas','Nahom','Natnael','Samuel','Yonas'];
        $femaleFirst = ['Abigiya','Arsema','Bezawit','Blen','Daniya','Eden','Efrata','Eldana','Eliana','Hana','Helen','Hilina','Kidist','Liya','Mahlet','Meklit','Nardos','Rahel','Sara','Tsion'];
        $lastNames = ['Abebe','Alemu','Amare','Asrat','Bekele','Birhanu','Dagne','Debella','Eshetu','Fikadu','Gebre','Girma','Haile','Kassa','Kebede','Mekonnen','Mulugeta','Tadesse','Tesfaye','Worku'];

        $studentRole = Role::where('name', 'student')->first();
        $admissionNum = 5001;
        $userCounter = 0;
        $created = 0;
        $skipped = 0;
        $studentsPerSection = 10;

        $gradeOrder = [9, 10, 11, 12];
        $sectionLetters = ['A', 'B', 'C', 'D'];
        $sectionCounters = [];
        foreach ($gradeOrder as $g) {
            foreach ($sectionLetters as $l) {
                $sectionCounters[$g][$l] = 0;
            }
        }

        foreach ($gradeOrder as $gradeNum) {
            $class = $lebuClasses[$gradeNum] ?? null;
            if (!$class) continue;

            foreach ($sectionLetters as $letter) {
                $sectionKey = $gradeNum . '_' . $letter;
                $section = $allSections[$sectionKey] ?? null;
                if (!$section) continue;

                for ($i = 1; $i <= $studentsPerSection; $i++) {
                    $isMale = ($i % 2 === 1);
                    $firstNames = $isMale ? $maleFirst : $femaleFirst;
                    $firstName = $firstNames[array_rand($firstNames)];
                    $lastName1 = $lastNames[array_rand($lastNames)];
                    $lastName2 = $lastNames[array_rand($lastNames)];
                    while ($lastName2 === $lastName1) { $lastName2 = $lastNames[array_rand($lastNames)]; }
                    $fullName = $firstName . ' ' . $lastName1 . ' ' . $lastName2;

                    $birthYear = 2025 - ($gradeNum + 5);
                    $birthMonth = str_pad(random_int(1, 12), 2, '0', STR_PAD_LEFT);
                    $maxDay = ($birthMonth == '02') ? 28 : 30;
                    $birthDay = str_pad(random_int(1, $maxDay), 2, '0', STR_PAD_LEFT);
                    $dob = $birthYear . '-' . $birthMonth . '-' . $birthDay;

                    $sectionCounters[$gradeNum][$letter]++;
                    $rollNumber = 'G' . $gradeNum . $letter . '-' . str_pad($sectionCounters[$gradeNum][$letter], 2, '0', STR_PAD_LEFT);
                    $admission = 'SOR/2025/' . str_pad($admissionNum, 4, '0', STR_PAD_LEFT);
                    $admissionNum++;
                    $genderLower = $isMale ? 'male' : 'female';

                    try {
                        $idNumber = 'STU-LEB-' . str_pad($userCounter + 1, 4, '0', STR_PAD_LEFT);
                        $email = $idNumber . '@redemption.edu';
                        $defaultPassword = str_replace('-', '', $dob);

                        $user = User::updateOrCreate(
                            ['email' => $email],
                            ['name' => $fullName, 'id_number' => $idNumber, 'password' => bcrypt($defaultPassword), 'role' => 'student', 'gender' => $genderLower, 'branch_id' => $branch->id, 'is_active' => true]
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
                                'gender' => $genderLower,
                                'admission_number' => $admission, 'admission_date' => '2025-09-01',
                                'date_of_birth' => $dob, 'guardian_name' => $lastName1 . ' ' . $lastName2,
                                'guardian_phone' => null, 'status' => 'active',
                            ]
                        );

                        $created++;
                    } catch (\Exception $e) {
                        $skipped++;
                        continue;
                    }
                }
            }
        }

        if ($skipped > 0) { $this->command->warn("    Skipped: {$skipped}"); }
        return $created;
    }
}
