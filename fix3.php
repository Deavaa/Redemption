<?php
require 'vendor/autoload.php';
 $app = require_once 'bootstrap/app.php';
 $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
 $kernel->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

function addCol($table, $col, $def) {
    try {
        $cols = [];
        foreach(DB::select("DESCRIBE $table") as $r) $cols[] = $r->Field;
        if(!in_array($col, $cols)) {
            DB::statement("ALTER TABLE $table ADD COLUMN $col $def");
            echo "  + $table.$col\n";
        }
    } catch(\Exception $e) { echo "  ERR $table.$col: ".$e->getMessage()."\n"; }
}

echo "=== Adding ALL missing columns ===\n";

// academic_years
addCol('academic_years','is_current',"TINYINT(1) DEFAULT 0");
addCol('academic_years','status',"ENUM('active','inactive') DEFAULT 'active'");
addCol('academic_years','start_date',"DATE NULL");
addCol('academic_years','end_date',"DATE NULL");
addCol('academic_years','eth_start_year',"INT NULL");
addCol('academic_years','eth_start_month',"INT NULL");
addCol('academic_years','eth_start_day',"INT NULL");
addCol('academic_years','eth_end_year',"INT NULL");
addCol('academic_years','eth_end_month',"INT NULL");
addCol('academic_years','eth_end_day',"INT NULL");

// terms
addCol('terms','academic_year_id',"BIGINT UNSIGNED NULL");
addCol('terms','is_current',"TINYINT(1) DEFAULT 0");
addCol('terms','status',"ENUM('active','inactive') DEFAULT 'active'");
addCol('terms','start_date',"DATE NULL");
addCol('terms','end_date',"DATE NULL");
addCol('terms','eth_start_year',"INT NULL");
addCol('terms','eth_start_month',"INT NULL");
addCol('terms','eth_start_day',"INT NULL");
addCol('terms','eth_end_year',"INT NULL");
addCol('terms','eth_end_month',"INT NULL");
addCol('terms','eth_end_day',"INT NULL");

// teachers
addCol('teachers','first_name',"VARCHAR(255) NOT NULL DEFAULT ''");
addCol('teachers','last_name',"VARCHAR(255) NOT NULL DEFAULT ''");
addCol('teachers','email',"VARCHAR(255) NULL");
addCol('teachers','phone',"VARCHAR(255) NULL");
addCol('teachers','qualification',"VARCHAR(255) NULL");
addCol('teachers','department',"VARCHAR(255) NULL");
addCol('teachers','hire_date',"DATE NULL");
addCol('teachers','salary',"DECIMAL(10,2) NULL");
addCol('teachers','status',"ENUM('active','inactive') DEFAULT 'active'");

// classes
addCol('classes','academic_year_id',"BIGINT UNSIGNED NULL");
addCol('classes','teacher_id',"BIGINT UNSIGNED NULL");
addCol('classes','capacity',"INT NULL");
addCol('classes','section',"VARCHAR(255) NULL");
addCol('classes','branch_id',"BIGINT UNSIGNED NULL");

// sections
addCol('sections','class_id',"BIGINT UNSIGNED NULL");
addCol('sections','max_students',"INT NULL");
addCol('sections','teacher_id',"BIGINT UNSIGNED NULL");

// subjects
addCol('subjects','code',"VARCHAR(100) NULL");
addCol('subjects','type',"ENUM('core','elective') DEFAULT 'core'");
addCol('subjects','description',"TEXT NULL");
addCol('subjects','status',"ENUM('active','inactive') DEFAULT 'active'");

// exams
addCol('exams','academic_year_id',"BIGINT UNSIGNED NULL");
addCol('exams','term_id',"BIGINT UNSIGNED NULL");
addCol('exams','type',"ENUM('midterm','final','quiz','assignment','project') DEFAULT 'midterm'");
addCol('exams','start_date',"DATE NULL");
addCol('exams','end_date',"DATE NULL");
addCol('exams','total_marks',"DECIMAL(8,2) DEFAULT 100");
addCol('exams','status',"ENUM('upcoming','ongoing','completed') DEFAULT 'upcoming'");
addCol('exams','description',"TEXT NULL");

// fees
addCol('fees','academic_year_id',"BIGINT UNSIGNED NULL");
addCol('fees','class_id',"BIGINT UNSIGNED NULL");
addCol('fees','amount',"DECIMAL(10,2) NOT NULL DEFAULT 0");
addCol('fees','type',"ENUM('tuition','lab','library','transport','sports','other') DEFAULT 'tuition'");
addCol('fees','due_date',"DATE NULL");
addCol('fees','status',"ENUM('active','inactive') DEFAULT 'active'");
addCol('fees','description',"TEXT NULL");

echo "\n=== Seeding ===\n";

// Academic Years
 $c = DB::table('academic_years')->count();
if($c == 0) {
    $ayIds = [];
    foreach([
        ['2015 EC (2022-2023 GC)','2022-09-11','2023-07-07',0],
        ['2016 EC (2023-2024 GC)','2023-09-10','2024-07-05',0],
        ['2017 EC (2024-2025 GC)','2024-09-08','2025-07-04',1],
    ] as $a) {
        $ayIds[] = DB::table('academic_years')->insertGetId([
            'name'=>$a[0],'start_date'=>$a[1],'end_date'=>$a[2],
            'is_current'=>$a[3],'status'=>'active',
            'created_at'=>now(),'updated_at'=>now()
        ]);
    }
    echo "OK: 3 Academic Years\n";
} else { echo "SKIP: Academic Years ($c)\n"; $ayIds = DB::table('academic_years')->pluck('id')->toArray(); }

// Terms
 $c = DB::table('terms')->count();
if($c == 0) {
    foreach([
        [$ayIds[2],'Semester 1','2024-09-08','2025-01-15',0],
        [$ayIds[2],'Semester 2','2025-02-01','2025-07-04',1],
    ] as $t) {
        DB::table('terms')->insert([
            'academic_year_id'=>$t[0],'name'=>$t[1],'start_date'=>$t[2],
            'end_date'=>$t[3],'is_current'=>$t[4],'status'=>'active',
            'created_at'=>now(),'updated_at'=>now()
        ]);
    }
    echo "OK: 2 Terms\n";
} else { echo "SKIP: Terms ($c)\n"; }

// Teachers
 $c = DB::table('teachers')->count();
if($c == 0) {
    foreach([
        ['Abebe','Kebede','abekebe@school.com','Mathematics'],
        ['Tigist','Hailu','thailu@school.com','Languages'],
        ['Dawit','Assefa','dassefa@school.com','Science'],
        ['Hanna','Tadesse','htadesse@school.com','Science'],
        ['Solomon','Girma','sgirma@school.com','Social Studies'],
        ['Meron','Desta','mdesta@school.com','Science'],
        ['Yared','Bekele','ybekele@school.com','Social Studies'],
        ['Selam','Teklu','steklu@school.com','Languages'],
        ['Bereket','Yohannes','byohannes@school.com','Computer'],
        ['Feven','Abraha','fabraha@school.com','Creative Arts'],
    ] as $t) {
        DB::table('teachers')->insert([
            'first_name'=>$t[0],'last_name'=>$t[1],'email'=>$t[2],
            'department'=>$t[3],'qualification'=>'BSc','status'=>'active',
            'created_at'=>now(),'updated_at'=>now()
        ]);
    }
    echo "OK: 10 Teachers\n";
} else { echo "SKIP: Teachers ($c)\n"; }

// Classes + Sections
 $c = DB::table('classes')->count();
if($c == 0) {
    $ayId = DB::table('academic_years')->where('is_current',1)->value('id');
    $tids = DB::table('teachers')->pluck('id')->toArray();
    for($g = 1; $g <= 12; $g++) {
        $cId = DB::table('classes')->insertGetId([
            'name'=>"Grade $g",'academic_year_id'=>$ayId,
            'teacher_id'=>$tids[$g-1] ?? null,'capacity'=>40,
            'created_at'=>now(),'updated_at'=>now()
        ]);
        foreach(['A','B'] as $s) {
            DB::table('sections')->insert([
                'class_id'=>$cId,'name'=>$s,'max_students'=>40,
                'teacher_id'=>$tids[array_rand($tids)] ?? null,
                'created_at'=>now(),'updated_at'=>now()
            ]);
        }
    }
    echo "OK: 12 Classes + 24 Sections\n";
} else { echo "SKIP: Classes ($c)\n"; }

// Subjects
 $c = DB::table('subjects')->count();
if($c == 0) {
    foreach(['Mathematics','English','Physics','Chemistry','Biology',
             'History','Geography','Civics','Amharic','Physical Education',
             'Computer Science','Art','Music','Technical Drawing'] as $s) {
        DB::table('subjects')->insert([
            'name'=>$s,'type'=>'core','status'=>'active',
            'created_at'=>now(),'updated_at'=>now()
        ]);
    }
    echo "OK: 14 Subjects\n";
} else { echo "SKIP: Subjects ($c)\n"; }

// Students
 $c = DB::table('students')->count();
if($c == 0) {
    $names = [
        ['Abel','Tadesse','male','2012-03-15'],['Bethlehem','Girma','female','2012-07-20'],
        ['Carlos','Mekonnen','male','2013-01-10'],['Diana','Haile','female','2013-05-25'],
        ['Ephrem','Solomon','male','2011-09-12'],['Fikir','Abebe','female','2011-02-18'],
        ['Girma','Dawit','male','2014-04-05'],['Helen','Teklu','female','2014-08-22'],
        ['Isaiah','Bekele','male','2015-06-14'],['Juliet','Assefa','female','2010-12-01'],
        ['Kidane','Yohannes','male','2010-03-28'],['Lidya','Desta','female','2009-07-15'],
        ['Mikiyas','Hailu','male','2008-01-20'],['Nardos','Kebede','female','2007-10-10'],
        ['Osman','Abraha','male','2007-05-05'],['Praise','Tekalign','female','2006-11-11'],
        ['Samuel','Gebre','male','2006-02-14'],['Tsion','Mulugeta','female','2005-08-08'],
        ['Ruth','Tadesse','female','2005-04-22'],['Yared','Worku','male','2005-01-15'],
    ];
    $cIds = DB::table('classes')->pluck('name','id')->toArray();
    $cArr = array_keys($cIds);
    foreach($names as $i => $n) {
        $cId = $cArr[($i % count($cArr))];
        DB::table('students')->insert([
            'first_name'=>$n[0],'last_name'=>$n[1],'gender'=>$n[2],
            'date_of_birth'=>$n[3],'email'=>strtolower($n[0]).'.s@school.com',
            'class_grade'=>$cIds[$cId],'section'=>($i%2==0)?'A':'B',
            'roll_number'=>'SOR-'.str_pad($i+1,4,'0',STR_PAD_LEFT),
            'address'=>'Addis Ababa','status'=>'active','admission_date'=>'2024-09-08',
            'created_at'=>now(),'updated_at'=>now()
        ]);
    }
    echo "OK: 20 Students\n";
} else { echo "SKIP: Students ($c)\n"; }

// Fees
 $c = DB::table('fees')->count();
if($c == 0) {
    $ayId = DB::table('academic_years')->where('is_current',1)->value('id');
    foreach([
        ['Tuition Fee',5000.00,'tuition'],['Lab Fee',500.00,'lab'],
        ['Library Fee',200.00,'library'],['Transport Fee',1500.00,'transport'],
        ['Sports Fee',300.00,'sports'],
    ] as $f) {
        DB::table('fees')->insert([
            'name'=>$f[0],'academic_year_id'=>$ayId,'amount'=>$f[1],
            'type'=>$f[2],'due_date'=>'2025-06-30','status'=>'active',
            'created_at'=>now(),'updated_at'=>now()
        ]);
    }
    echo "OK: 5 Fees\n";
} else { echo "SKIP: Fees ($c)\n"; }

// Exams
 $c = DB::table('exams')->count();
if($c == 0) {
    $ayId = DB::table('academic_years')->where('is_current',1)->value('id');
    $tIds = DB::table('terms')->pluck('id')->toArray();
    foreach([
        ['Midterm Exam Sem1','midterm','2024-11-15','2024-11-25','completed',$tIds[0]??null],
        ['Final Exam Sem1','final','2025-01-10','2025-01-25','completed',$tIds[0]??null],
        ['Midterm Exam Sem2','midterm','2025-04-10','2025-04-20','upcoming',$tIds[1]??null],
        ['Final Exam Sem2','final','2025-06-15','2025-06-30','upcoming',$tIds[1]??null],
    ] as $e) {
        DB::table('exams')->insert([
            'name'=>$e[0],'academic_year_id'=>$ayId,'term_id'=>$e[5],
            'type'=>$e[1],'start_date'=>$e[2],'end_date'=>$e[3],
            'total_marks'=>100,'status'=>$e[4],
            'created_at'=>now(),'updated_at'=>now()
        ]);
    }
    echo "OK: 4 Exams\n";
} else { echo "SKIP: Exams ($c)\n"; }

// Settings
foreach([
    'school_name'=>'School of Redemption','school_slogan'=>'Where Dreams Take Flight',
    'school_phone'=>'+251-11-123-4567','school_email'=>'info@schoolofredemption.com',
    'school_address'=>'Addis Ababa, Bole Sub-City, Ethiopia',
    'about_mission'=>'To provide transformative education.',
    'about_vision'=>'To be a premier educational institution.',
] as $k => $v) {
    DB::table('settings')->updateOrInsert(['key'=>$k],['value'=>$v,'created_at'=>now(),'updated_at'=>now()]);
}
echo "OK: Settings\n";

echo "\n=== DONE ===\n";
echo "Login: admin@school.com / admin123\n";
