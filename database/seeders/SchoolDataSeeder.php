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
     * ONE unified seeder. ALL student data is HARDCODED inside this file.
     * No external SQL files needed. No other student seeders required.
     *
     * Campuses:
     *   - Lebu Campus: Secondary School (G9-12) - 642 real students, Sections A-D
     *   - Tuludimtu Campus: Primary School (G1-8) - Section A only
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
        //    Tuludimtu: G1-8 (Section A only)
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
        //    Tuludimtu: Only Section A per grade (8 sections)
        //    Lebu: Sections A, B, C, D per grade (16 sections)
        // ======================================================================
        $allSections = [];

        foreach ($tuludimtuClasses as $gradeNum => $class) {
            $key = $gradeNum . '_A';
            $allSections[$key] = Section::updateOrCreate(
                ['class_id' => $class->id, 'name' => 'Section A'],
                ['max_students' => 50, 'teacher_id' => null]
            );
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
        $this->command->info('  Sections: 24 (Tuludimtu: 8xA, Lebu: 16xA-D)');

        // ======================================================================
        // 9. STUDENTS - ALL 642 Lebu High School students HARDCODED
        //    No external SQL files. Everything is in THIS file.
        // ======================================================================
        $lebuStudentData = [
            ['Youliyan Kitaw Mulugeta', 'Female', '2002-08-06'],
            ['Melat Yimneshnesh Girma', 'Female', '2010-01-15'],
            ['Eldana Misganaw Hone', 'Female', '2003-11-07'],
            ['Kalid Mohammed Mohammednur', 'Male', '2002-04-14'],
            ['Edidya Kidmealem Tilahun', 'Female', '2003-03-13'],
            ['Hilana Habtamu Mekonnen', 'Female', '2003-04-29'],
            ['Faris Ahmed Obesa', 'Female', '2011-05-23'],
            ['Nafirom Debella Ebisso', 'Female', '2004-12-20'],
            ['Abigiya Tesfaye Fikadu', 'Female', '2003-07-20'],
            ['Esrom Tesfaye Tafesse', 'Male', '2003-02-17'],
            ['Hilina Yonas Sisay', 'Female', '2003-06-15'],
            ['Milka Belachew Kidane', 'Female', '2003-07-27'],
            ['Hena Tefera Wakene', 'Female', '2004-02-29'],
            ['Tsion Alemayehu Mekonnen', 'Female', '2010-10-16'],
            ['Bezawit Tekleselasse Mamo', 'Female', '2003-05-07'],
            ['Suher Ramy Semir', 'Female', '2011-03-18'],
            ['Sabrin Saleh Ali', 'Female', '2004-11-02'],
            ['Mikir Smamaw Tadese', 'Female', '2002-08-15'],
            ['Kidus Pawlos Tesfa', 'Male', '2003-06-24'],
            ['Honaliyat Tamirat Tafese', 'Female', '2004-02-18'],
            ['Selman Nasser Adem', 'Male', '2011-05-25'],
            ['Nataniyem Abinet Tesfaye', 'Male', '2002-09-28'],
            ['Meseret Ayalew Meselu', 'Female', '2003-04-15'],
            ['Wintana Yisehak Kebede', 'Female', '2010-04-17'],
            ['Natenael Assaminew Gebeyehu', 'Male', '2010-03-11'],
            ['Lidya Elias Erku', 'Female', '2003-10-21'],
            ['Amen Aane Yirga', 'Female', '2003-03-11'],
            ['Yemareyam Yonas Gebrekirstos', 'Female', '2003-01-22'],
            ['Ahlam Abdu Mohammed', 'Female', '2010-07-16'],
            ['Abemelk Henok Sisay', 'Male', '2009-07-23'],
            ['Abenezer Yonas Bahiru', 'Male', '2002-10-13'],
            ['Amanuel Hagazi Fikadu', 'Male', '2003-02-18'],
            ['Saleh Kemal Saleh', 'Male', '2009-06-16'],
            ['Matiyas Haylay Abreha', 'Male', '2001-05-09'],
            ['Michael Dereje Kebede', 'Male', '2003-03-12'],
            ['Mihreteab Yilikal Atnafu', 'Male', '2011-07-20'],
            ['Efrata Alemayehu Worku', 'Female', '2002-02-17'],
            ['Gelila Birhay Yadesa', 'Female', '2002-04-18'],
            ['Meklit Daniel Shebarge', 'Female', '2003-06-03'],
            ['Nuhamin Biruk Ermias', 'Female', '2003-06-22'],
            ['Hasinat Jemal Umer', 'Female', '2002-02-07'],
            ['Robel Alebachew Alemnew', 'Male', '2009-11-22'],
            ['Eldana Seyfe Nigussie', 'Female', '2003-02-24'],
            ['Amanuel Fitsum Gebremedhin', 'Male', '1998-10-21'],
            ['Abigiya Esubalew Beshawred', 'Female', '2010-06-15'],
            ['Daniya Ashenafi Masresha', 'Female', '2003-11-10'],
            ['Henok Wondwossen Agezew', 'Male', '2002-10-13'],
            ['Horenus Abubeker Seid', 'Male', '2002-01-27'],
            ['Ibrahim Mohammed Seid', 'Male', '2010-06-06'],
            ['Negalign Fikadu Gibraga', 'Male', '2000-06-03'],
            ['Barkot Reta Demsu', 'Male', '2001-12-18'],
            ['Biruk Mare Kide', 'Male', '2003-07-24'],
            ['Barkot Yoseph Teshome', 'Male', '2002-02-21'],
            ['Yesef Shiferaw Damte', 'Male', '2000-03-08'],
            ['Nahom Taye Dema', 'Male', '2000-03-12'],
            ['Yohana Tilahun Lemma', 'Female', '2010-06-04'],
            ['Huda Mohammed Yusuf', 'Female', '2010-07-22'],
            ['Bitanya Seyoum Getachew', 'Female', '2011-12-25'],
            ['Dagim Shemsu Dumer', 'Male', '2002-08-05'],
            ['Natnael Solomon Misganaw', 'Male', '2002-12-23'],
            ['Liya Beniam Ayalew', 'Female', '2011-12-13'],
            ['Eden Birhanu Yiblte', 'Female', '2001-04-10'],
            ['Hemen Taye Getaneh', 'Female', '2010-01-10'],
            ['Soliyana Bayu Tadesse', 'Female', '2011-02-04'],
            ['Betsinat Samson Zewdie', 'Female', '2003-03-10'],
            ['Nardos Biruk Ermias', 'Female', '2003-06-22'],
            ['Suher Awel Zeinu', 'Female', '2003-12-19'],
            ['Sibhat Tesfahun temesgen', 'Female', '2004-04-10'],
            ['Hiwot Worku Getahun', 'Female', '2001-04-22'],
            ['Summer Mohammed Hussen', 'Female', '2011-02-24'],
            ['Efrata Abraraw Tegegne', 'Female', '2003-04-05'],
            ['Bitaniya Girma Motuma', 'Female', '2003-03-17'],
            ['Endiya Gena Girsha', 'Female', '2003-06-13'],
            ['Saron Teku Gutema', 'Female', '2010-01-27'],
            ['Leul Amare Tuffa', 'Female', '2001-07-20'],
            ['selehadin Aminu Mohammed', 'Male', '2002-08-30'],
            ['Abel Zeray Tekelemariam', 'Male', '2009-08-08'],
            ['Estifanos Zewde Estifanos', 'Male', '2003-01-17'],
            ['Yeab Belete Alemu', 'Male', '2011-03-09'],
            ['Tesfanesh Regasa Adugna', 'Female', '2004-12-15'],
            ['Rihana Abduljelil Wulle', 'Female', '2003-02-05'],
            ['Sena Taye Kebede', 'Female', '2002-03-28'],
            ['Soliyana Kibrom Feshaye', 'Female', '2009-05-18'],
            ['Ephrata Yemane Gebremedhin', 'Female', '2011-05-17'],
            ['Abel Million Getachew', 'Male', '2003-02-10'],
            ['Yamin Gebresilassie Gebrekidan', 'Male', '2003-03-02'],
            ['Nahom Getnet Shebarge', 'Male', '2003-06-09'],
            ['Sunamawit Yohannes Birhane', 'Female', '2011-10-18'],
            ['Hilina Tebebu Bekele', 'Female', '2003-05-13'],
            ['Dagim Bekele Birhanu', 'Male', '2001-08-09'],
            ['Kalkidan Amaslu Desalegn', 'Female', '2004-12-22'],
            ['Menna Tsegaye Begashaw', 'Female', '2011-01-15'],
            ['Elsama Samuel Legesse', 'Male', '2002-08-11'],
            ['Senrina Tesfahiwot Goyitom', 'Male', '2008-01-10'],
            ['Sumeya Kedir Shifa', 'Female', '2002-02-01'],
            ['Kidus Chernet Eshetu', 'Male', '2010-01-09'],
            ['Akeelah Abayneh Mekonnen', 'Female', '2011-08-03'],
            ['Biruk Lakew Tegegn', 'Male', '2001-12-19'],
            ['Soliyana Tsegaye Alemayehu', 'Female', '2003-08-08'],
            ['Tsion Abebaw Dagne', 'Female', '2003-08-21'],
            ['Mekdalawit Mehare Tekeste', 'Female', '2003-05-13'],
            ['Yididya Girma Ejersa', 'Female', '2002-02-28'],
            ['Melahier Tedros Mebratu', 'Female', '2009-09-12'],
            ['Nejila Osman Mohammed', 'Female', '2010-12-04'],
            ['Blen Wondimagegn Worku', 'Female', '2010-11-26'],
            ['Khadija Sultan Khadir', 'Female', '2002-06-19'],
            ['Bitsiet Ayele Alemu', 'Female', '2004-07-08'],
            ['Tsion Tafere Kasawun', 'Female', '2010-08-12'],
            ['Eyosyas Robel Getachew', 'Male', '2008-09-17'],
            ['Dagm Yoftahe Feleke', 'Male', '2003-03-02'],
            ['Eyuel Mekuanent Getachew', 'Male', '2010-07-20'],
            ['Akiya Guadeneh Tesfa', 'Male', '2003-07-02'],
            ['Yohannes Derbew Admasu', 'Male', '2002-02-28'],
            ['Ehab Befkadu Wohab', 'Male', '2011-05-14'],
            ['Michael Alemayehu Kabtimer', 'Male', '2003-08-19'],
            ['Samson Wubante Melese', 'Male', '2010-09-11'],
            ['Nahom Getebo Lire', 'Male', '2011-07-23'],
            ['Mikiyas Befkadu Zeleke', 'Male', '2003-03-08'],
            ['Sara Hussen Adem', 'Female', '2011-05-11'],
            ['Maramawit Abel Araya', 'Female', '2010-03-22'],
            ['Yonathan Natnael Yenealem', 'Male', '2003-06-15'],
            ['Meri Habtemariam Kfle', 'Female', '2008-05-19'],
            ['Nardos Birhanu Adamu', 'Female', '2010-05-11'],
            ['Neda Mensur Berhe', 'Female', '2010-12-29'],
            ['Arsema Efrem Ekubay', 'Female', '2002-07-14'],
            ['Danayit Gebrehiwot Gebrekirstos', 'Female', '2003-03-28'],
            ['Rahel Zelalem Tilahun', 'Female', '2003-03-21'],
            ['Natnael Wosenu Abera', 'Male', '2010-06-04'],
            ['Eyuel Bereket Tezera', 'Male', '2003-07-03'],
            ['Yohana Shewalem Zerfu', 'Female', '2010-09-19'],
            ['Amen Solomon Mekonnen', 'Female', '2010-05-11'],
            ['Yididiya Tesfagabr Asgdom', 'Female', '2003-07-16'],
            ['Bilise Niguse Ture', 'Female', '2009-03-13'],
            ['Hilary Sileshi Tigabe', 'Female', '2011-03-11'],
            ['Hanan Mickiyas Murad', 'Female', '2011-03-09'],
            ['Kidan Melese Alemu', 'Female', '2010-08-28'],
            ['Mahlet Birhane Masresha', 'Female', '2010-08-22'],
            ['Delina Tedros Kesete', 'Female', '2010-08-14'],
            ['Luwam Teklehaymanot Teweldemedhen', 'Female', '2009-01-17'],
            ['Selina Dejen Etsegenet', 'Female', '2002-09-12'],
            ['Kawal Kadi Jundi', 'Female', '2010-08-27'],
            ['Koket Teshome Debella', 'Male', '2003-06-19'],
            ['Mohammed khalid Osman', 'Male', '2009-03-24'],
            ['Alvaro Yoseoh Venicho', 'Male', '2001-06-05'],
            ['Rushda Abdu Mohammed', 'Female', '2004-03-15'],
            ['Abigiya Dawit Woldeyohannes', 'Female', '2003-02-14'],
            ['Yohanna Belachew Bekele', 'Female', '2001-06-17'],
            ['Rami Abdu Hamid', 'Male', '2002-10-30'],
            ['Kidus Birhanu Bedada', 'Male', '2002-11-22'],
            ['Imran Musema Nasir', 'Male', '2003-07-04'],
            ['Eyuel Israel Zeleke', 'Male', '2002-01-23'],
            ['Nuhamin Desalegn Degu', 'Female', '2011-06-13'],
            ['Tigist Meseret Fente', 'Female', '2001-04-08'],
            ['Yunus Ismael Yasin', 'Male', '2001-11-17'],
            ['Bilen Tadesse Hordofa', 'Female', '2011-06-10'],
            ['Mahlet Tilahun Abate', 'Female', '2002-01-10'],
            ['Eyueal Abebaw Alemu', 'Male', '2010-03-24'],
            ['Eyuel Ayele Teshome', 'Male', '2003-06-21'],
            ['Abel Kiflay Leake', 'Male', '2003-04-08'],
            ['Yonathan Daniel Hailemariam', 'Male', '2003-06-10'],
            ['Eliyab Daniel Tesfamichael', 'Male', '2002-09-27'],
            ['Aser Teshome Kifle', 'Male', '2003-08-10'],
            ['Semir Abubeker Urga', 'Male', '2003-01-22'],
            ['Aliya Musefa Ibrahim', 'Female', '2002-05-05'],
            ['Danawit Bekalu Genete', 'Male', '2010-09-09'],
            ['Yohannes Ashenafi Tesgaye', 'Male', '2002-07-19'],
            ['Mihreteab Tilahun Basazen', 'Male', '2002-09-20'],
            ['Eyana Abreham Gibane', 'Female', '2002-05-23'],
            ['Hitamar Mussei Adbib', 'Female', '2011-12-17'],
            ['Eliyana Zerihun Bayissa', 'Female', '2004-06-29'],
            ['Elshaday Endalemaw Kase', 'Female', '2003-11-29'],
            ['Etsubdink Adefris Ayalew', 'Female', '2002-08-16'],
            ['Yabsira Hirpa Abera', 'Male', '2002-10-23'],
            ['Mikreselam Simon Zekewos', 'Male', '2002-09-21'],
            ['Nathan Mebratu Girmay', 'Male', '2010-05-06'],
            ['Leul Abeselom Hagos', 'Male', '2002-02-09'],
            ['Maria Mesfin Bogale', 'Female', '2002-07-20'],
            ['Rediet Tadesse Gebeyehehu', 'Female', '2003-02-06'],
            ['Hebron Yohannes Mulugeta', 'Male', '2003-01-30'],
            ['Christian Bekele Habte', 'Female', '2002-02-17'],
            ['Efrata Bekele Birhanu', 'Female', '2003-05-25'],
            ['Haset Alehegn Tilahun', 'Female', '2011-04-02'],
            ['Eldan a Zelalem Bayew', 'Female', '2002-07-12'],
            ['Kalkidan Getachew wendimu', 'Female', '2011-03-17'],
            ['Adoniyas Ermias Geremew', 'Male', '2003-06-13'],
            ['Saron Solomon Yemane', 'Female', '2010-08-20'],
            ['Kidus Solomon Tesfaye', 'Male', '2010-04-27'],
            ['Hamdi Omar Nur', 'Male', '2008-10-23'],
            ['Amen Sewale Ahimeles', 'Male', '2010-07-10'],
            ['Amanuel Daniel Solomon', 'Male', '2003-01-04'],
            ['Mahlet Dereje Kitaw', 'Female', '2003-01-30'],
            ['Refif Muhamed Ali', 'Male', '2003-07-06'],
            ['Metadel Ezekiel Saketa', 'Female', '2003-07-16'],
            ['Asinat Jula Edamo', 'Female', '2003-08-04'],
            ['Liya Birhanu Tesfaye', 'Female', '2009-01-04'],
            ['Ydidiya Bereket Temesgan', 'Female', '2009-05-22'],
            ['Carmel Mekbib Getachew', 'Female', '2011-08-27'],
            ['Mahlet Lemessa Demise', 'Female', '2001-03-11'],
            ['Habtamu Yalew Kebede', 'Male', '2002-06-18'],
            ['Bethelhem Tefera Shibeshi', 'Female', '2002-10-09'],
            ['Suerafel Grumie Mekonnen', 'Male', '2011-09-17'],
            ['Gediyon Abraham Yohanned', 'Male', '2009-01-03'],
            ['Mihireteab Towfick Girma', 'Male', '2001-12-13'],
            ['Bersabe Asrat Tesfaye', 'Female', '2000-01-21'],
            ['Moti Efa Muleta', 'Male', '2002-09-29'],
            ['Meklit Wubshet Hailesslase', 'Female', '2000-10-03'],
            ['Dagmawit Birhanu Tasaw', 'Female', '2000-10-03'],
            ['Mohammed Ahmed Digo', 'Male', '2002-03-22'],
            ['Yanet Bazezew Tesfaye', 'Female', '2003-01-12'],
            ['Ephrata Tesfaye Giday', 'Female', '2002-02-13'],
            ['Amir Sultan Sebir', 'Male', '2002-03-23'],
            ['Bereket Birku Asrat', 'Male', '2001-09-10'],
            ['Abubeker Nursema Degefe', 'Male', '1999-10-13'],
            ['Nathan Getachew Betemariam', 'Male', '2001-06-15'],
            ['Eyob Admasu Anthene', 'Male', '2000-02-13'],
            ['Dawit Yeshak Girma', 'Male', '2002-04-03'],
            ['Yabsira Tafese Mena', 'Male', '2002-07-06'],
            ['Abubeker Jula Edmo', 'Male', '2000-11-04'],
            ['Nader Negash Wuhab', 'Male', '2002-06-11'],
            ['Ahmed Muhammed Idris', 'Male', '2007-09-15'],
            ['Million Habtamu Chaka', 'Male', '1999-01-11'],
            ['Leul Ermiyas Abate', 'Male', '2002-10-03'],
            ['Ruth Yonas Abrha', 'Female', '2007-10-19'],
            ['Samuel Mekbib Amare', 'Male', '2002-03-12'],
            ['Nadiya Mohammed Omer', 'Female', '2001-09-03'],
            ['Soliyana Sahlemariam Mezmur', 'Female', '2002-06-12'],
            ['Yusra Mohammed Seid', 'Female', '2009-03-22'],
            ['Hlina Kassahun Eticha', 'Female', '2002-01-03'],
            ['Tsion Teshome Yaie', 'Female', '2002-08-09'],
            ['Sharon Nebiyou Paulos', 'Female', '2002-12-11'],
            ['Eliana Gezahegn Teshome', 'Female', '2002-06-20'],
            ['Rodas Sintayehu Getachew', 'Female', '2002-09-25'],
            ['Kidus Abrham Tesfaye', 'Male', '2002-02-17'],
            ['Makda Maru Getu', 'Female', '2002-03-18'],
            ['Haron Yitagesu Abebe', 'Male', '2001-09-21'],
            ['Samrawit Minasse Taddese', 'Female', '2001-10-22'],
            ['Alazar Mulugeta Zewudu', 'Male', '2009-10-22'],
            ['Arsema Belay Teni', 'Female', '2001-08-02'],
            ['Haniya Muzie Mohammed', 'Female', '2002-01-17'],
            ['Melat Kumsa Mati', 'Female', '2001-03-13'],
            ['Tsion Balachew Gebre', 'Female', '2001-12-19'],
            ['Lielt Ermiyas Abate', 'Female', '2002-10-03'],
            ['Meysun Nasir Nurhussen', 'Female', '2001-09-11'],
            ['Mariya Tilahun Belay', 'Female', '2001-06-26'],
            ['Heran W/gergish T/medhin', 'Female', '2002-06-07'],
            ['Lidiya Yemane Gebremedhin', 'Female', '2009-07-08'],
            ['Salma Mohammed Seid', 'Male', '2000-07-20'],
            ['Natanan Asefa Gebremichael', 'Male', '2001-10-05'],
            ['Brook Matiwos Debasu', 'Male', '2001-05-07'],
            ['Kirubel Samuel gebreghziabher', 'Male', '2002-07-16'],
            ['Bereket Aynalem Gebeyehu', 'Male', '2002-09-25'],
            ['Yafet Getachew Biratu', 'Male', '2002-04-19'],
            ['Biruk Yoseph Robele', 'Male', '2002-06-22'],
            ['Kghalid Abdujelil Yiman', 'Male', '2002-02-12'],
            ['Saron Mitiku Guluma', 'Female', '2002-11-19'],
            ['Bamlak Mesay Tamiru', 'Female', '2002-07-21'],
            ['Zemichael Yonas Tamire', 'Male', '2010-03-10'],
            ['Mohammed Kedir Ahmed', 'Male', '2002-06-30'],
            ['Sumeya Ahmed Hussen', 'Female', '2001-08-21'],
            ['Oumer Mohammed Ahmed', 'Male', '2002-08-02'],
            ['Abdulhamid Zeki Mohammed', 'Male', '2002-04-04'],
            ['Peniel Tedros Mebrhatu', 'Female', '2008-02-04'],
            ['Hiermiela Mehari Tewelde', 'Female', '2009-04-15'],
            ['Hareg Haftom Woldu', 'Female', '1999-08-01'],
            ['Eyuel Birhanu Yadesa', 'Male', '2001-04-18'],
            ['Biruk Getachew teferi', 'Male', '2002-01-01'],
            ['Esmail Akmel Dennur', 'Male', '2001-09-21'],
            ['Kaleab Dires Mekonnen', 'Male', '2001-05-30'],
            ['Eyosyas Aderaw Mekonnen', 'Male', '2002-05-17'],
            ['Raphael Alemseged Gebrewold', 'Male', '2002-01-29'],
            ['Letera Lema Tefera', 'Male', '2001-02-04'],
            ['Yosefe Samson Getachew', 'Male', '2001-04-27'],
            ['Leul Misrakdingle Sahlemariam', 'Male', '2009-08-04'],
            ['Sabra Mesfin Abebe', 'Male', '2010-08-22'],
            ['Tsegab Zemene Daniel', 'Male', '2002-11-19'],
            ['Bisrat Dawde Nassir', 'Male', '2009-10-30'],
            ['Fesha Weldit Gebreindrias', 'Male', '1999-02-03'],
            ['Dawit Feleke Wolde', 'Male', '2002-05-14'],
            ['Hawi Tesfaye Medeksa', 'Female', '2002-08-21'],
            ['Makda Tsegaye Aregawi', 'Female', '2001-05-12'],
            ['Sara Mekuanent Getachew', 'Female', '2010-04-14'],
            ['Khalid Zeki Kerga', 'Male', '2002-04-04'],
            ['Yonatan Matro Delphin', 'Male', '2001-01-18'],
            ['Ruth Daniel Byene', 'Female', '2009-09-01'],
            ['Bitaniya Tefera Zeleke', 'Female', '2002-05-24'],
            ['Soliyana Samuel Hunde', 'Female', '2002-08-14'],
            ['Amen Mengistu Seyoum', 'Female', '2002-07-30'],
            ['Fikir Yigermal Zelalem', 'Male', '2002-10-11'],
            ['Yeabsera Garedew Haile', 'Male', '2001-09-01'],
            ['Eden Godana Nigatu', 'Female', '2001-05-18'],
            ['Kaleb Muzie Yishake', 'Male', '2002-09-26'],
            ['Sara Nasser Adem', 'Female', '2010-01-13'],
            ['Christian Dawit Molalign', 'Male', '2003-01-30'],
            ['Esrom Bereket Lema', 'Male', '2001-05-22'],
            ['Elim Tesfit Kidane', 'Female', '2009-06-19'],
            ['Helen Demissie Tesfaye', 'Female', '2002-01-28'],
            ['Nolawit Tilahun Tadesse', 'Female', '2009-09-25'],
            ['Mykey Tsegaab Tulu', 'Male', '2008-10-10'],
            ['Dagmawit Mechal Hailu', 'Male', '2002-10-06'],
            ['Amen Daniel Yhidego', 'Female', '2000-11-22'],
            ['Beserat Biniyam Kitila', 'Male', '2001-09-17'],
            ['Jehiachin Mengistu Legesse', 'Male', '2009-04-07'],
            ['Eyuel Yosef Gebregezabher', 'Male', '2010-02-24'],
            ['Yohannes Bedlu Belayneh', 'Male', '2002-05-09'],
            ['Glory Abebayehu Deme', 'Female', '2010-04-21'],
            ['Bitaniya Yisak Sebhat', 'Female', '2002-08-14'],
            ['Senae Awel Zeinu', 'Female', '2008-06-20'],
            ['Mikiyas Daniel Tamirat', 'Male', '2010-05-19'],
            ['Tesfaye Habtu Biyadgelegn', 'Male', '2007-03-01'],
            ['Sofoniyas Mezgebu Akalu', 'Male', '2010-03-15'],
            ['Tsion Mulubirhan Worku', 'Female', '2009-07-21'],
            ['Selam Hailemariam Gebresilase', 'Female', '2009-07-15'],
            ['Elilita Tsegaye Begashaw', 'Female', '2009-01-09'],
            ['Eldana Samuel Kahsay', 'Female', '2009-02-09'],
            ['Atronos Samson Kiflemaryam', 'Female', '2008-04-29'],
            ['Sewsen Mohammed Esmael', 'Female', '2001-01-23'],
            ['Nardos Ephrem Amare', 'Female', '2002-07-30'],
            ['Soliana Nigussie Shibru', 'Female', '2010-01-30'],
            ['Lidiya Abebe Mekonnen', 'Female', '2002-05-24'],
            ['Aklesiya Getachew Mandefro', 'Female', '2002-09-11'],
            ['Soliyana Tsegaye Wolde', 'Female', '2002-08-28'],
            ['Bemnet Mekonene Feye', 'Female', '2010-11-12'],
            ['Abenezer Wondessen Kebede', 'Male', '2002-06-20'],
            ['Eyasu Esayas Tsegaye', 'Male', '2002-12-23'],
            ['Hevelom Abebe Banjaw', 'Male', '2000-01-13'],
            ['Belay Tesfaye Mideksa', 'Male', '2002-08-21'],
            ['Firomsa Seyfu Asfaw', 'Male', '2000-01-19'],
            ['Nahom Tewdros Teble', 'Male', '2001-12-16'],
            ['Samir Mohammed Ibrahim', 'Male', '2000-09-27'],
            ['Nathnael Benyam Tadesse', 'Male', '2010-07-19'],
            ['Fitsum Alemayehu Bezabih', 'Male', '2009-12-16'],
            ['Winmtana Zemichael Wahsom', 'Female', '2002-11-05'],
            ['Ruth Tegegnework Chanyalew', 'Female', '2000-09-02'],
            ['Lewi Lisanu Kebede', 'Male', '2003-03-01'],
            ['Wiliam Bezene Gidey', 'Male', '2000-03-10'],
            ['Efrata Temesgen Gidyelew', 'Female', '2001-01-11'],
            ['Soliyana Daniel Jemam', 'Female', '2003-12-17'],
            ['Bereket Asrat Aragaw', 'Female', '2002-06-04'],
            ['Lina Birhanu Tesfaye', 'Female', '2009-01-03'],
            ['Rebira Adane Fita', 'Male', '2009-04-25'],
            ['Yisakor Tewdros Asgedom', 'Male', '2012-04-13'],
            ['Basim Mohammed Jemal', 'Male', '2002-09-30'],
            ['Eyuel Getachew Gashu', 'Male', '2003-02-07'],
            ['Efrata Admasu Asefa', 'Female', '2002-11-25'],
            ['Adonay Mulat Kelalu', 'Male', '2000-06-16'],
            ['Mikiyas Meaza Weldegerima', 'Male', '2001-12-12'],
            ['Seyfu Wadi Shiferaw', 'Male', '2001-06-20'],
            ['Abel Fiseha Giday', 'Male', '2002-02-16'],
            ['Kaleb Girmay Berhe', 'Male', '2001-05-06'],
            ['Leul Alemayehu Bezabih', 'Male', '2011-04-04'],
            ['Naife Mohammed Adem', 'Male', '2002-04-09'],
            ['Nathan Getachew Men gesha', 'Male', '2008-09-27'],
            ['Sirak Dawit Tesfamichael', 'Male', '2002-09-27'],
            ['Natan Yoftahe Feleke', 'Male', '2002-10-17'],
            ['Natinael Daniel Bedada', 'Male', '2002-12-24'],
            ['Nigat Abreham Kibreab', 'Female', '2008-09-02'],
            ['Sosena Fantaw Shewaye', 'Female', '2001-09-04'],
            ['Amanuel Abel Solomon', 'Male', '2010-08-20'],
            ['Adyan Sultan Mohammed', 'Male', '2009-05-25'],
            ['Eudegeua Bekele Deboch', 'Male', '2009-02-02'],
            ['Dawit Bahru Ashenafi', 'Male', '2001-02-23'],
            ['Kidus Belete Alemu', 'Male', '2002-06-09'],
            ['Rahma Elias Kedir', 'Female', '2002-05-25'],
            ['Mayet Afework Hailemaryam', 'Female', '2002-09-28'],
            ['Barock Yonas Admasu', 'Male', '2001-08-24'],
            ['Christian Natnael Yenealem', 'Male', '2001-11-09'],
            ['Noel Yekunoamlak Engidawork', 'Male', '2009-03-21'],
            ['Selihom Getnet Girma', 'Male', '2009-02-23'],
            ['Tsedenoya Anteneh Getnet', 'Female', '2000-10-01'],
            ['Kisanet Kibreab Teame', 'Female', '2008-05-10'],
            ['Melat Elias Melese', 'Female', '2001-06-23'],
            ['Rewina Hailu Gebru', 'Female', '2002-08-01'],
            ['Elroe Tibebu Assefa', 'Female', '1999-06-16'],
            ['Nebila Khalid Abdu', 'Female', '2009-06-14'],
            ['Danayt Getahun Guhes', 'Female', '2000-06-07'],
            ['Fiyona demoz Habtom', 'Female', '2007-10-21'],
            ['Rodas Netsanet Lewsged', 'Female', '2009-03-07'],
            ['Rakeb Ayalkebet Beyene', 'Female', '2000-10-20'],
            ['Yinomiya Desta Genoro', 'Male', '2009-10-25'],
            ['Adnan Abdu Hussen', 'Male', '2002-03-13'],
            ['Abubeker Mohammed Ahmed', 'Male', '2002-02-12'],
            ['Christina Mamush Abera', 'Female', '2002-01-26'],
            ['Veronica Sisay Dagne', 'Female', '2002-06-21'],
            ['Hala Aden Ahmed', 'Female', '2001-08-05'],
            ['Asanti Jemal Mohammed', 'Female', '2002-01-14'],
            ['Simren Teshome Kiduro', 'Female', '2002-02-12'],
            ['Alazar Daawit Chernet', 'Male', '2010-06-21'],
            ['Amanuel Fikre Wolde', 'Male', '2001-09-27'],
            ['Naod Amanuel Hailemariam', 'Male', '2001-08-02'],
            ['Naod Shiferaw Bereda', 'Male', '1998-11-29'],
            ['Nafeyad Dawit Tibebu', 'Male', '2000-12-21'],
            ['Sisay Tesfaye Mideksa', 'Male', '2000-05-19'],
            ['Ayub Muzein Sani', 'Male', '2007-05-30'],
            ['Chali Geneti Dufera', 'Male', '2001-12-22'],
            ['Nebil Mohammed Abdulqadir', 'Male', '2008-02-03'],
            ['Arsema Yonas Gebrekristos', 'Female', '2002-02-11'],
            ['Mezmure Yared Teklu', 'Male', '1999-08-16'],
            ['Samuel Yimneshnesh Girma', 'Male', '2000-12-13'],
            ['Yonas Tedla Bekele', 'Male', '2001-06-01'],
            ['Kaleb Teshome Yaie', 'Male', '2001-06-27'],
            ['Derartu Bikila Negaw', 'Female', '2000-11-12'],
            ['Nadiya Omar Nur', 'Female', '2000-11-10'],
            ['Betelihem Zeru Kahasay', 'Female', '1998-05-29'],
            ['Yordanos Tatek Tulu', 'Female', '2000-05-11'],
            ['Ruth Biruk Abdisa', 'Female', '2002-05-04'],
            ['Betlhem Yoseph Tesfaye', 'Female', '2000-07-15'],
            ['Fenet Hussen Obsa', 'Female', '2008-08-16'],
            ['Abrham Getachew Yitena', 'Male', '2000-04-05'],
            ['Hamdi Robel Yemaneh', 'Male', '1999-02-21'],
            ['Abdurhaman Abdullah Dulo', 'Male', '1999-09-17'],
            ['Befikir Lemma Bizuneh', 'Male', '2002-07-04'],
            ['Eldana Fekadu Abebe', 'Female', '2001-09-05'],
            ['Khyrat Ashenafi Dawnud', 'Female', '1999-10-30'],
            ['Elshday Mulatu Abdecho', 'Female', '2001-12-26'],
            ['Maya Tesfaye Teame', 'Female', '1999-11-14'],
            ['Tsion Chernet Begashaw', 'Female', '2000-02-21'],
            ['Dansuma Roba Hnalo', 'Female', '1999-09-18'],
            ['Michael Amare Deniber', 'Male', '2005-10-19'],
            ['Nahom Teshome Hailemariam', 'Male', '2008-05-05'],
            ['Hasset Zerihun Getasew', 'Female', '2007-08-16'],
            ['Kidus Yoseph Getachew', 'Male', '2000-05-16'],
            ['Hawi Chala Gutu', 'Female', '2002-05-12'],
            ['Amar Kadi Juni', 'Male', '1998-06-23'],
            ['Hlina Yibeltal Fekadu', 'Female', '2001-06-07'],
            ['Biruk Ababa Bogale', 'Male', '2001-10-03'],
            ['Getahun Alene Delele', 'Male', '2001-03-16'],
            ['Afomiya Tesfaye Weldehana', 'Female', '2001-02-25'],
            ['Muaz Abdulkadir Kassie', 'Male', '1999-10-30'],
            ['Wintana Mussie Petros', 'Female', '2001-02-05'],
            ['Yanet Belay Weldesemayat', 'Female', '2001-07-22'],
            ['Fethya Mohammed Tsega', 'Female', '2008-02-10'],
            ['Amjed salah Ali', 'Male', '2008-07-17'],
            ['Biruk Girma Sarawit', 'Male', '1999-11-03'],
            ['Natan Lesanwork Siyum', 'Male', '1999-02-10'],
            ['Yeabsira Asfaw Tadesse', 'Male', '2000-12-16'],
            ['Meron Samuel Worku', 'Female', '2001-08-11'],
            ['Abenezer Mechal Hailu', 'Male', '2001-03-03'],
            ['Aymen Sani Oumer', 'Male', '2000-11-18'],
            ['Mohammed Abubeker Seid', 'Male', '2000-12-30'],
            ['Biniyam Ashenafi Tsegaye', 'Male', '2001-01-01'],
            ['Michael Aregay liben', 'Male', '2001-05-10'],
            ['Elleni Elias Engda', 'Female', '2001-08-12'],
            ['Keneni Ture Halake', 'Female', '2000-08-03'],
            ['Rodas Embiale Belay', 'Female', '2001-02-17'],
            ['Saron Tadesse Girma', 'Female', '2008-09-07'],
            ['Bitaniya Alemayehu Hailu', 'Female', '2009-04-24'],
            ['Zemariam Mintesnot Getachew', 'Female', '2002-01-02'],
            ['Eyuel Bereket Bireda', 'Male', '2001-03-09'],
            ['Sima Redwan Mohammed', 'Female', '2010-01-03'],
            ['Esey Samuel Fitsum', 'Male', '2001-02-04'],
            ['Obsinan Adnan Sadik', 'Male', '1997-10-14'],
            ['Natanim Bafrdu Bezabih', 'Male', '2001-10-05'],
            ['Betselot Hassahun Geda', 'Female', '2002-02-05'],
            ['Betiel Weldegergish Tewledemedhen', 'Female', '2003-05-05'],
            ['Bitaniya Getachew Muluye', 'Female', '2001-11-06'],
            ['Aymen Ibrahim Adem', 'Male', '2000-11-07'],
            ['Ahmedin Mohammed Aman', 'Male', '2008-08-09'],
            ['Sinit Solomon Yemane', 'Female', '1999-12-20'],
            ['Yitayish Habte Wale', 'Female', '2002-09-12'],
            ['Dinna Adnew Gebre', 'Female', '2000-10-24'],
            ['Rediet Syfe Habtemariam', 'Female', '2001-06-04'],
            ['Awet Asmerom okubay', 'Male', '1998-09-24'],
            ['Eyob Tsegaye Aregawi', 'Male', '1997-08-17'],
            ['Melal Gezu Demse', 'Female', '1999-08-11'],
            ['Arsema Nereaya Sisshaye', 'Female', '2001-05-18'],
            ['Biruk Firew Kassa', 'Male', '2001-09-05'],
            ['Naol Teshome Debella', 'Male', '2001-08-29'],
            ['Yididya Yirgalem Chernet', 'Female', '2002-02-26'],
            ['Joshua Yeshitla Tekletsadik', 'Male', '2000-12-18'],
            ['Dagim Eondwosen Abate', 'Male', '2000-04-30'],
            ['Mohammed Sheref Mohamed', 'Male', '2001-10-22'],
            ['Yeabsera Yohannes Berhane', 'Female', '2001-09-07'],
            ['Hana Samuel Nedaw', 'Female', '2002-03-15'],
            ['Yonatan Abera Geremariam', 'Male', '2001-07-28'],
            ['Esrom Habtemichael Okubit', 'Male', '1999-11-17'],
            ['Abenezer Amare Mamo', 'Male', '1999-03-09'],
            ['Yafet Tadesse Worku', 'Male', '2000-04-22'],
            ['Leul Gebresillasie Abrum', 'Male', '2000-08-28'],
            ['Amenu Samson Admasu', 'Male', '1999-05-01'],
            ['Abel Getachew Mulugeta', 'Male', '2000-01-15'],
            ['Huzeyfa Kemal Hussen', 'Male', '2000-01-11'],
            ['Ismael Degelo Dekebo', 'Male', '2001-02-12'],
            ['Sima Redwan Mohammed', 'Female', '2003-12-26'],
            ['Emanuella Tewdros Ayenew', 'Female', '2002-04-29'],
            ['Yeabsira Zinabe Fentaw', 'Female', '1999-07-15'],
            ['Nueba Amnuel Iyasu', 'Female', '2001-06-02'],
            ['Tamirat Mossie Abawa', 'Male', '2000-07-19'],
            ['Eddie Mesfin Bogale', 'Female', '2000-11-24'],
            ['Jemal Yusuf Al-Amin', 'Male', '2002-03-01'],
            ['Abel Atinkut Yalew', 'Male', '2000-12-06'],
            ['Mikael Solomon Wordofa', 'Male', '2000-06-12'],
            ['Yeab Bereket Tezera', 'Female', '2001-03-13'],
            ['Christian Ayele Alemu', 'Male', '2002-12-19'],
            ['Nahom Abebelign Mekuria', 'Male', '2001-05-15'],
            ['Biruk Esayas Kebede', 'Male', '2001-07-05'],
            ['Bemnet Ashenafi Ayalew', 'Male', '2001-08-03'],
            ['Dagmawi Tibebu Lemma', 'Male', '2001-08-28'],
            ['Sebaif Debella Ebbiso', 'Male', '2001-02-06'],
            ['Firdows Kedir Marhaba', 'Female', '2001-06-06'],
            ['Samuel Tesfageber Asgdom', 'Male', '2000-05-16'],
            ['Christian Wondwossen Kassa', 'Female', '2001-04-05'],
            ['Nebiyou Yonas Tamire', 'Male', '2001-06-02'],
            ['Yeabsira Tesfahun Temesgen', 'Male', '1999-02-28'],
            ['Melika Shekur Essa', 'Female', '2000-10-10'],
            ['Elias Mesfin Anamo', 'Male', '2000-05-21'],
            ['Yesaron Birhanu Worku', 'Female', '2002-12-23'],
            ['Eldana Gaym Birhane', 'Female', '2001-04-29'],
            ['Kirubel Wondwossen Berhanu', 'Male', '2001-05-21'],
            ['Saeed Ali Seid', 'Male', '2001-06-29'],
            ['Yanet Gedeon Yohannes', 'Female', '2002-12-12'],
            ['Dagmawi Kassahun Ejigu', 'Male', '2000-09-13'],
            ['Lina Henk Engida', 'Female', '2002-01-05'],
            ['Kalem Ashenafi Eshetu', 'Male', '2000-09-23'],
            ['Obsan Girma Legesse', 'Male', '1999-12-24'],
            ['Edon Habtom tekeste', 'Female', '2001-05-28'],
            ['Aymen Muhajer Edlu', 'Male', '2000-05-11'],
            ['Dibora Nebiyu Sherishe', 'Female', '1999-11-02'],
            ['Maramawit Alemu Belayneh', 'Female', '2000-08-16'],
            ['Felcita Mark Wawero', 'Female', '1999-07-07'],
            ['Yonas Mhretab Tewelde', 'Male', '1999-10-20'],
            ['Natnael Taye Demissie', 'Male', '2000-08-25'],
            ['Wintana Sameso Keflmariyam', 'Female', '1999-03-12'],
            ['Abigiya Workaferahu Bogale', 'Female', '2000-10-05'],
            ['Yared Kebede Yirga', 'Male', '1999-02-13'],
            ['Mohammed Hassen Abdella', 'Male', '1999-10-10'],
            ['Kenean Tadesse Gebru', 'Male', '2000-06-23'],
            ['Emran Jemal Seid', 'Male', '1999-12-15'],
            ['Muse Abreham Sentaywe', 'Male', '2000-12-23'],
            ['Arsema Birhane Mebratu', 'Female', '1999-10-28'],
            ['Ismael Ibrahim Idris', 'Male', '1999-08-18'],
            ['Sofoniyas Wendmigegn Tadess', 'Male', '1998-09-27'],
            ['Honey Mekonnen Feye', 'Female', '2000-09-03'],
            ['Besufikad Teshome Gurmu', 'Male', '1998-01-27'],
            ['Samuel Fikadu Tesema', 'Male', '1999-12-15'],
            ['Gudeta Ghodana Nigatu', 'Male', '1998-07-07'],
            ['Maeruf Musema Nasir', 'Male', '2000-04-15'],
            ['Yohannes Yetsedaw Assefa', 'Male', '2000-09-04'],
            ['Bisrat Araya Hadush', 'Male', '1999-04-27'],
            ['Ashenafi Hailemariam Kassa', 'Male', '1999-07-07'],
            ['Yosefe Tadesse Mola', 'Male', '2000-01-16'],
            ['Rebifen Zenebe Wamii', 'Female', '1999-03-09'],
            ['Yerosen Abebe Ayana', 'Female', '1999-11-13'],
            ['Maramawit Wushet H/marriyam', 'Female', '2000-09-18'],
            ['Ofumma Zeki Geleta', 'Male', '2000-08-03'],
            ['Nebiyu Samuel Fitsum', 'Male', '2000-01-16'],
            ['Nebil Zeki Mohammed', 'Male', '2000-04-04'],
            ['Nahom Solomon Yilma', 'Male', '2000-07-16'],
            ['Selman Tadesse G/mariam', 'Female', '1999-06-04'],
            ['Biruk Genene Yilma', 'Male', '1999-01-13'],
            ['Saron Amare Zeleke', 'Female', '2001-02-09'],
            ['Adil Kemal Sulyman', 'Male', '1999-08-06'],
            ['Fuad Mohammed Tsega', 'Male', '2000-01-10'],
            ['Meriem Beriso Abishu', 'Female', '2002-11-11'],
            ['Anisa Awel Zeinu', 'Female', '2000-11-04'],
            ['Ferhana Mohammed Omer', 'Female', '1999-09-04'],
            ['Abdulkerim Nasir Hajiboka', 'Female', '1998-10-24'],
            ['Efrata Daniel Siolomon', 'Female', '2001-01-10'],
            ['Marta Beriso Abishu', 'Female', '1999-09-09'],
            ['Yerosen Gosaye Dandena', 'Female', '1999-12-21'],
            ['Yordanos Biyadg Belste', 'Female', '1999-08-14'],
            ['Marta Beriso Abishu', 'Female', '1997-04-10'],
            ['Soliyana Degie Fentie', 'Female', '1998-06-25'],
            ['Rediet Kinde Temesgen', 'Female', '1998-10-15'],
            ['Wngelawit Zemere Zewwide', 'Female', '1998-08-23'],
            ['Kidus Yared W/michael', 'Male', '1999-10-15'],
            ['Elnathal Amare Workiye', 'Male', '1998-08-16'],
            ['Absalat Abebe Banjaw', 'Female', '1999-01-11'],
            ['Elezer Tadesse Zewdie', 'Male', '1999-06-25'],
            ['Rist Kassa Agegnew', 'Female', '1998-06-15'],
            ['Sarem Kifle Gebresenbet', 'Female', '2000-04-21'],
            ['Biruk Yoseph Abebalem', 'Female', '2000-03-12'],
            ['Dagmawi Seifu Mekuria', 'Male', '2008-05-21'],
            ['Barok Wondimu Hailemeskel', 'Male', '2008-08-09'],
            ['Joshua Abebayehu Deme', 'Male', '1999-10-01'],
            ['Abenezer Bekele Shitaye', 'Male', '2007-08-10'],
            ['Siham Abubeker Urga', 'Female', '2000-10-05'],
            ['Maedot Kassahun Gebreeyesus', 'Female', '2000-08-20'],
            ['Sebrin Elias Kedir', 'Female', '2000-09-15'],
            ['Heldana Zerihun Bayissa', 'Female', '2001-03-29'],
            ['Luli Mohammed Hummed', 'Female', '1999-07-05'],
            ['Semhal Meaza Woldegerima', 'Female', '2006-01-11'],
            ['Sebrina Ibrahim Adem', 'Female', '1999-06-19'],
            ['Eyoas Demeke Tsehay', 'Male', '2001-01-04'],
            ['Sebrina Khalid Abadi', 'Female', '2000-08-08'],
            ['Blen Mekuria Abebe', 'Female', '1999-05-13'],
            ['Rediet Abyot Hailu', 'Female', '2001-01-29'],
            ['Meron Nardos Abadi', 'Female', '2001-05-16'],
            ['Hermella Solomon Ameneshewa', 'Female', '2000-01-01'],
            ['Surafel Berhanu Shafi', 'Male', '2000-08-09'],
            ['Bereket Ayalew Adamu', 'Male', '2006-01-09'],
            ['Misgana Solomon Sileshi', 'Female', '1999-11-19'],
            ['Yohana Bekele Lemma', 'Female', '2001-01-28'],
            ['Yeabsira Ashenafi Legesse', 'Male', '2003-10-21'],
            ['eyerusalem Abebaw Dagne', 'Female', '2000-02-29'],
            ['Eyob Nega Weldesenbet', 'Male', '1999-10-12'],
            ['Robsen Nigatu Kassa', 'Male', '2006-03-06'],
            ['Yusra Nasir Fuli', 'Female', '2007-09-13'],
            ['Samrawit Biruk Gebre', 'Female', '1999-09-18'],
            ['Hana Fikadu Bekele', 'Female', '1999-06-10'],
            ['Meseret Gezu Baheru', 'Female', '1999-12-13'],
            ['Roza Amene Gata', 'Female', '2000-09-10'],
            ['Meseret Abebe Alem', 'Female', '2000-05-05'],
            ['Horenus Asefa Gebremichael', 'Male', '1999-09-29'],
            ['Nazrawit Ashebir Mulugeta', 'Female', '1997-12-29'],
            ['Biruk Dait Berhane', 'Male', '2000-10-06'],
            ['Ruth Habtamu Demissie', 'Female', '2000-10-01'],
            ['Kalkidan Birhanu Wabi', 'Female', '1999-12-16'],
            ['Edna Fekadu Dinku', 'Female', '2000-09-07'],
            ['Lidiya Hailu Kassa', 'Female', '2000-05-15'],
            ['Firehiwot Hailemariyam Temesgen', 'Female', '2000-03-16'],
            ['Yerosen Teshome Demisu', 'Female', '1999-06-11'],
            ['Luna Dawit Tesfamichael', 'Female', '2000-06-03'],
            ['Meseret Beyene Terefe', 'Female', '2000-01-26'],
            ['Brhane Mehari Tewelde', 'Male', '1999-08-10'],
            ['Nejat Kemal Saleh', 'Female', '1998-05-22'],
            ['Lidya Mussie Kidane', 'Female', '2000-05-19'],
            ['Ruphtana Michael Birhane', 'Female', '2001-01-24'],
            ['Elsabet Teshome Teldessa', 'Female', '2000-05-20'],
            ['Gebriela Alemseged G/Wold', 'Female', '1999-08-04'],
            ['Bisrat Seyfemichael Mulugeta', 'Male', '2000-02-04'],
            ['Zetseat Gessese Kurabachew', 'Female', '2000-11-23'],
            ['Sifen Asrat Aragaw', 'Female', '2000-06-08'],
            ['Rodas Solomon Tassew', 'Female', '2000-02-23'],
            ['Eyuel Beidemariam Fiseha', 'Male', '2000-11-23'],
            ['Nesredin Zeynu Shikur', 'Male', '2000-05-25'],
            ['Semon Yosef Ahmed', 'Male', '2000-11-21'],
            ['Rediet Tefera Shibeshi', 'Male', '2000-01-05'],
            ['Alazar Habtamu Sime', 'Male', '2000-06-08'],
            ['Nahom Wondwossen Adone', 'Male', '2000-12-12'],
            ['Kaleb Hailu Gebru', 'Male', '2000-01-15'],
            ['Absalat Terefe Ageze', 'Female', '1999-08-03'],
            ['Fraole Kassahun Lemma', 'Male', '2000-06-06'],
            ['Selim Musbah Kedir', 'Male', '2000-10-16'],
            ['Nardos Yohannes Sime', 'Female', '2000-09-25'],
            ['Nahom Henok Alemu', 'Male', '2007-04-04'],
            ['Bilisuma Ejeru Nigussie', 'Male', '2007-05-06'],
            ['Kidus Mehari Tekeste', 'Male', '1999-03-23'],
            ['Ayantu Tesfaye Asnake', 'Female', '1999-03-22'],
            ['Kaleb Gedion Lelago', 'Male', '2000-02-03'],
            ['Biruk Asres Ewnetu', 'Male', '2000-11-24'],
            ['Yididya Daniel Jemam', 'Female', '2000-01-22'],
            ['Eyob Kinfemichael Demissie', 'Male', '2008-10-13'],
        ];

        $lebuStudentCount = $this->createStudents(
            $lebuBranch, $lebuClasses, $allSections, $ay, $lebuStudentData, 'Lebu'
        );
        $this->command->info("  Lebu students: {$lebuStudentCount} (G9-12)");

        // ======================================================================
        // 10. TEACHER ASSIGNMENTS (Lebu G9-12)
        // ======================================================================
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

            foreach ($lebuSectionLetters as $letter) {
                $secKey = $gradeNum . '_' . $letter;
                if (!isset($allSections[$secKey])) continue;
                TeacherAssignment::updateOrCreate(
                    ['teacher_id' => $teacher->id, 'class_id' => $lebuClasses[$gradeNum]->id, 'section_id' => $allSections[$secKey]->id, 'subject_id' => $subject->id, 'academic_year_id' => $ay->id],
                    []
                );
                $assignCount++;
            }

            TeacherAssignment::updateOrCreate(
                ['teacher_id' => $teacher->id, 'class_id' => $lebuClasses[$gradeNum]->id, 'section_id' => null, 'subject_id' => $subject->id, 'academic_year_id' => $ay->id],
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
    // STUDENT CREATION METHOD
    // Distributes students across G9-12 round-robin, balanced A-D sections
    // ======================================================================
    private function createStudents(
        Branch $branch,
        array $lebuClasses,
        array &$allSections,
        AcademicYear $ay,
        array $studentData,
        string $label = 'Lebu'
    ): int {
        $this->command->newLine();
        $this->command->info("  Seeding {$label} students...");
        $totalRaw = count($studentData);
        $this->command->info("  Loaded {$totalRaw} student records");

        // Filter invalid DOBs
        $validStudents = [];
        $invalidCount = 0;
        foreach ($studentData as $row) {
            $birthYear = (int) substr($row[2], 0, 4);
            if ($birthYear < 1995 || $birthYear > 2015) {
                $invalidCount++;
                continue;
            }
            $validStudents[] = $row;
        }
        if ($invalidCount > 0) {
            $this->command->info("  Filtered {$invalidCount} invalid DOBs");
        }

        usort($validStudents, fn($a, $b) => strcmp($a[2], $b[2]));
        $totalValid = count($validStudents);
        $this->command->info("  Processing {$totalValid} valid students...");

        $gradeOrder = [9, 10, 11, 12];
        $lebuSectionLetters = ['A', 'B', 'C', 'D'];
        $lebuSections = [];
        foreach ($gradeOrder as $g) {
            $lebuSections[$g] = [];
            foreach ($lebuSectionLetters as $idx => $letter) {
                $key = $g . '_' . $letter;
                if (isset($allSections[$key])) {
                    $lebuSections[$g][$idx] = $allSections[$key];
                }
            }
        }

        $gradeCounters = [];
        foreach ($gradeOrder as $g) {
            $gradeCounters[$g] = array_fill(0, count($lebuSections[$g]), 0);
        }

        $admissionNum = 3001;
        $studentRole = Role::where('name', 'student')->first();
        $userCounter = 0;
        $created = 0;
        $skipped = 0;
        $gradeIdx = 0;
        $sectionCounts = [
            9 => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0],
            10 => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0],
            11 => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0],
            12 => ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0],
        ];

        foreach ($validStudents as $row) {
            $fullName = $row[0];
            $gender = $row[1];
            $dob = $row[2];

            try {
                $gradeNum = $gradeOrder[$gradeIdx % count($gradeOrder)];
                $gradeIdx++;

                $class = $lebuClasses[$gradeNum] ?? null;
                if (!$class) { $skipped++; continue; }

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
                if (!$section) { $skipped++; continue; }

                $gradeCounters[$gradeNum][$sectionIdx]++;
                $sectionLetter = chr(65 + $sectionIdx);
                $seqNum = $gradeCounters[$gradeNum][$sectionIdx];
                $rollNumber = 'G' . $gradeNum . $sectionLetter . '-' . str_pad($seqNum, 2, '0', STR_PAD_LEFT);
                $admission = 'SOR/2025/' . str_pad($admissionNum, 4, '0', STR_PAD_LEFT);
                $admissionNum++;
                $genderLower = strtolower($gender);

                $nameParts = explode(' ', $fullName);
                $lastName = count($nameParts) >= 2 ? $nameParts[count($nameParts) - 1] : $nameParts[0];
                $secondName = count($nameParts) >= 3 ? $nameParts[1] : $lastName;
                $guardianName = $secondName . ' ' . $lastName;

                $idNumber = 'STU-' . date('Y') . '-' . str_pad($userCounter + 1, 4, '0', STR_PAD_LEFT);
                $email = $idNumber . '@redemption.edu';
                $defaultPassword = str_replace('-', '', $dob);

                $user = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => $fullName, 'id_number' => $idNumber,
                        'password' => bcrypt($defaultPassword), 'role' => 'student',
                        'gender' => $genderLower,
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

                Student::updateOrCreate(
                    ['admission_number' => $admission],
                    [
                        'user_id' => $user->id, 'full_name' => $fullName,
                        'branch_id' => $branch->id, 'class_id' => $class->id,
                        'section_id' => $section->id, 'academic_year_id' => $ay->id,
                        'gender' => $genderLower,
                        'roll_number' => $rollNumber, 'admission_date' => '2025-09-01',
                        'date_of_birth' => $dob, 'guardian_name' => $guardianName,
                        'guardian_phone' => null, 'status' => 'active',
                    ]
                );

                $created++;
                $sectionCounts[$gradeNum][$sectionLetter]++;

            } catch (\Exception $e) {
                $skipped++;
                continue;
            }
        }

        $this->command->newLine();
        $this->command->info("    {$label} students created: {$created}");
        $this->command->info("    User accounts: {$userCounter}");
        if ($skipped > 0) {
            $this->command->warn("    Skipped: {$skipped}");
        }

        foreach ($gradeOrder as $g) {
            $parts = [];
            $total = 0;
            foreach ($lebuSectionLetters as $l) {
                $c = $sectionCounts[$g][$l];
                $parts[] = "{$l}:{$c}";
                $total += $c;
            }
            $this->command->info("      Grade {$g}: {$total} (" . implode(' ', $parts) . ")");
        }
        $this->command->info('    Login: email=<idNumber>@redemption.edu, password=DOB');

        return $created;
    }
}
