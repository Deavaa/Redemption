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
     * Seeds 121 real students into the system, assigning them to the correct
     * grade/section based on their date of birth.
     *
     * Grade assignment (Academic Year 2025/2026):
     *   Grade 1 → born 2018-2019  (age 6-7)
     *   Grade 2 → born 2017-2018  (age 7-8)
     *   Grade 3 → born 2016-2017  (age 8-9)
     *   Grade 4 → born 2015-2016  (age 9-10)
     *   Grade 5 → born 2014-2015  (age 10-11)
     *   Grade 6 → born 2013-2014  (age 11-12)
     *   Grade 7 → born 2012-2013  (age 12-13)
     *   Grade 8 → born ≤2011      (age 13+)
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding real student data (121 students)...');

        // ── Resolve foreign-key dependencies ──────────────────────────
        $branch = Branch::where('is_headquarters', true)->first()
            ?? Branch::first();

        $ay = AcademicYear::where('is_current', true)->first()
            ?? AcademicYear::first();

        if (!$branch || !$ay) {
            $this->command->error('  ✗ No branch or academic year found. Run SchoolDataSeeder first.');
            return;
        }

        $this->command->info("  Using Branch: {$branch->name} (ID: {$branch->id})");
        $this->command->info("  Using AY: {$ay->name} (ID: {$ay->id})");

        // Build grade → class/section maps
        $allClasses = ClassRoom::where('branch_id', $branch->id)
            ->where('academic_year_id', $ay->id)
            ->get();

        $this->command->info("  Found {$allClasses->count()} classes in database:");
        foreach ($allClasses as $c) {
            $this->command->info("    - ID:{$c->id} Name:{$c->name} NumericName:{$c->numeric_name}");
        }

        // Key by numeric_name (more reliable), fallback to regex from name
        $classes = $allClasses->keyBy(function ($c) {
            if ($c->numeric_name) {
                return (int) $c->numeric_name;
            }
            if (preg_match('/(\d+)/', $c->name, $m)) {
                return (int) $m[1];
            }
            return 0;
        })->filter(fn($c, $k) => $k > 0);

        $this->command->info("  Parsed class map (grade → class_id): " . $classes->map(fn($c, $k) => "G{$k}→ID{$c->id}")->implode(', '));

        // Build sections per class
        $sectionsByClass = [];
        foreach ($classes as $gradeNum => $class) {
            $secs = Section::where('class_id', $class->id)
                ->orderBy('name')
                ->get()
                ->values();
            $sectionsByClass[$gradeNum] = $secs;
            $this->command->info("    Grade {$gradeNum} → Class ID:{$class->id} → {$secs->count()} sections");
        }

        // ── Auto-create missing classes & sections ──────────────────
        // The user wants ALL students imported. If a grade's class/section
        // doesn't exist yet, create it on the fly so no student is skipped.
        for ($g = 1; $g <= 8; $g++) {
            if (!isset($classes[$g])) {
                $class = ClassRoom::updateOrCreate(
                    [
                        'branch_id'         => $branch->id,
                        'academic_year_id'  => $ay->id,
                        'numeric_name'      => $g,
                    ],
                    [
                        'name'     => 'Grade ' . $g,
                        'capacity' => 40,
                    ]
                );
                $classes[$g] = $class;
                $this->command->info("    ✓ Auto-created Grade {$g} (Class ID:{$class->id})");
            }

            // Also try to load sections that may have been created after the initial scan
            if (!isset($sectionsByClass[$g]) || $sectionsByClass[$g]->isEmpty()) {
                $class = $classes[$g];
                $secs = Section::where('class_id', $class->id)
                    ->orderBy('name')
                    ->get()
                    ->values();

                if ($secs->isEmpty()) {
                    // Create sections matching SchoolDataSeeder format: "Section A", "Section B"
                    $secs = collect();
                    foreach (['A', 'B'] as $letter) {
                        $sec = Section::updateOrCreate(
                            [
                                'class_id' => $class->id,
                                'name'     => 'Section ' . $letter,
                            ],
                            [
                                'max_students' => 40,
                            ]
                        );
                        $secs->push($sec);
                    }
                    $this->command->info("    ✓ Auto-created sections for Grade {$g}: Section A, Section B");
                }

                $sectionsByClass[$g] = $secs;
            }
        }

        if ($classes->isEmpty()) {
            $this->command->error('  ✗ No classes found. Run SchoolDataSeeder first.');
            return;
        }

        // ── Delete demo students from SchoolDataSeeder ────────────────
        // SchoolDataSeeder creates dummy students without user_id, which
        // can conflict with our real students. Remove them first.
        $demoCount = Student::whereNull('user_id')->count();
        if ($demoCount > 0) {
            Student::whereNull('user_id')->delete();
            $this->command->info("  ✓ Deleted {$demoCount} demo students (no user_id) from SchoolDataSeeder");
        }

        // Also delete students with admission numbers starting from SOR/2025/1XXX (SchoolDataSeeder range)
        // but keep SOR/2025/2XXX+ (our real data range)
        $demoAdmissions = Student::where('admission_number', 'LIKE', 'SOR/2025/1%')->count();
        if ($demoAdmissions > 0) {
            Student::where('admission_number', 'LIKE', 'SOR/2025/1%')->delete();
            $this->command->info("  ✓ Deleted {$demoAdmissions} demo students (admission SOR/2025/1XXX)");
        }

        // ── Student raw data ──────────────────────────────────────────
        $students = [
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

        // ── Grade assignment logic ────────────────────────────────────
        $gradeCounters = [];
        $admissionNum = 3001; // Start at 3001 to avoid conflict with SchoolDataSeeder (1001-2XXX)
        $userCounter = 0;
        $created = 0;
        $skipped = 0;
        $errors = [];

        // Pre-cache the student role
        $studentRole = Role::where('name', 'student')->first();

        foreach ($students as $row) {
            [$fullName, $gender, $dob, $phone] = $row;

            try {
                // Calculate grade from DOB
                $birthYear = (int) substr($dob, 0, 4);
                $gradeNum = max(1, min(8, 2025 - $birthYear - 5));

                // Find the class for this grade
                $class = $classes[$gradeNum] ?? null;
                if (!$class) {
                    $this->command->warn("  ⚠ No class found for Grade {$gradeNum} — skipping {$fullName}");
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
                    $this->command->warn("  ⚠ No section for Grade {$gradeNum} — skipping {$fullName}");
                    $skipped++;
                    continue;
                }

                $gradeCounters[$gradeNum][$sectionIdx]++;

                $sectionLetter = $sectionIdx === 0 ? 'A' : 'B';
                // Use G{grade}{section}{counter} format to guarantee uniqueness across grades
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

                // ── Create User account (required by students.user_id FK) ──
                $idNumber = 'STU-' . date('Y') . '-' . str_pad($userCounter + 1, 4, '0', STR_PAD_LEFT);
                $email = $idNumber . '@redemption.edu';
                $defaultPassword = str_replace('-', '', $dob); // e.g. 20180806

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
                // Set email_verified_at separately (not in $fillable)
                if (!$user->email_verified_at) {
                    $user->email_verified_at = now();
                    $user->save();
                }
                $userCounter++;

                // Assign Spatie 'student' role if not already assigned
                if ($studentRole && !$user->roles()->where('role_id', $studentRole->id)->exists()) {
                    $user->roles()->attach($studentRole->id);
                }

                // ── Create Student record ──────────────────────────────────
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
                $errMsg = $fullName . ': ' . $e->getMessage();
                $errors[] = $errMsg;
                $this->command->warn("  ✗ Error importing {$fullName}: " . $e->getMessage());
                // Continue with next student instead of stopping
                continue;
            }
        }

        // ── Summary ───────────────────────────────────────────────────
        $this->command->newLine();
        $this->command->info("  ✓ Students created/updated: {$created}");
        $this->command->info("  ✓ User accounts created: {$userCounter}");
        if ($skipped) {
            $this->command->warn("  ⚠ Students skipped/errored: {$skipped}");
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
        $gradeLabels = [1 => 'Grade 1', 2 => 'Grade 2', 3 => 'Grade 3', 4 => 'Grade 4',
                        5 => 'Grade 5', 6 => 'Grade 6', 7 => 'Grade 7', 8 => 'Grade 8'];
        foreach ($gradeCounters as $gradeNum => $counts) {
            $total = $counts[0] + $counts[1];
            $this->command->info("    {$gradeLabels[$gradeNum]}: {$total} students (A:{$counts[0]} B:{$counts[1]})");
        }

        $this->command->newLine();
        $this->command->info('  Student login: email = <idNumber>@redemption.edu, password = DOB (e.g. 20180806)');
        $this->command->info('🎉 Student data seeded successfully!');
    }
}
