<?php
require 'vendor/autoload.php';
 $app = require_once 'bootstrap/app.php';
 $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
 $kernel->bootstrap();
use Illuminate\Support\Facades\DB;

// Show current columns
echo "=== Students table columns ===\n";
 $cols = [];
foreach(DB::select("DESCRIBE students") as $r) {
    $cols[] = $r->Field;
    echo "  {$r->Field} ({$r->Type}) Null={$r->Null} Default={$r->Default}\n";
}

// Add all missing columns
 $add = [
    'first_name' => "VARCHAR(255) NULL AFTER id",
    'last_name' => "VARCHAR(255) NULL AFTER first_name",
    'class_grade' => "VARCHAR(255) NULL",
    'section' => "VARCHAR(255) NULL",
    'roll_number' => "VARCHAR(255) NULL",
    'admission_date' => "DATE NULL",
    'notes' => "TEXT NULL",
];

echo "\n=== Adding missing columns ===\n";
foreach($add as $col => $def) {
    if(!in_array($col, $cols)) {
        try {
            DB::statement("ALTER TABLE students ADD COLUMN $col $def");
            echo "  + $col\n";
        } catch(\Exception $e) { echo "  ERR $col: ".substr($e->getMessage(),0,80)."\n"; }
    } else { echo "  OK: $col\n"; }
}

// Make nullable
 $makeNull = ['email','phone','date_of_birth','gender','address','guardian_name','guardian_phone'];
foreach($makeNull as $col) {
    if(in_array($col, $cols)) {
        try { DB::statement("ALTER TABLE students MODIFY $col VARCHAR(255) NULL"); } catch(\Exception $e) {}
    }
}
if(in_array('date_of_birth', $cols)) {
    try { DB::statement("ALTER TABLE students MODIFY date_of_birth DATE NULL"); } catch(\Exception $e) {}
}
if(in_array('notes', $cols)) {
    try { DB::statement("ALTER TABLE students MODIFY notes TEXT NULL"); } catch(\Exception $e) {}
}
if(in_array('address', $cols)) {
    try { DB::statement("ALTER TABLE students MODIFY address TEXT NULL"); } catch(\Exception $e) {}
}

// Now seed
echo "\n=== Seeding students ===\n";
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
        $data = [
            'gender'=>$n[2],'date_of_birth'=>$n[3],
            'class_grade'=>$cIds[$cId],'section'=>($i%2==0)?'A':'B',
            'roll_number'=>'SOR-'.str_pad($i+1,4,'0',STR_PAD_LEFT),
            'address'=>'Addis Ababa','status'=>'active','admission_date'=>'2024-09-08',
            'created_at'=>now(),'updated_at'=>now(),
            'first_name'=>$n[0],'last_name'=>$n[1],
            'email'=>strtolower($n[0]).'.s@school.com',
        ];
        // Only include columns that exist
        $existing = [];
        foreach(DB::select("DESCRIBE students") as $r) $existing[] = $r->Field;
        $filtered = array_intersect_key($data, array_flip($existing));
        DB::table('students')->insert($filtered);
    }
    echo "OK: 20 Students\n";
} else { echo "SKIP: Students ($c)\n"; }

// Settings
foreach([
    'school_name'=>'School of Redemption','school_slogan'=>'Where Dreams Take Flight',
    'school_phone'=>'+251-11-123-4567','school_email'=>'info@schoolofredemption.com',
    'school_address'=>'Addis Ababa, Bole Sub-City, Ethiopia',
] as $k => $v) {
    DB::table('settings')->updateOrInsert(['key'=>$k],['value'=>$v,'created_at'=>now(),'updated_at'=>now()]);
}
echo "OK: Settings\n";
echo "\n=== DONE ===\n";
