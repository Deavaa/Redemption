<?php
require 'vendor/autoload.php';
 $app = require_once 'bootstrap/app.php';
 $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
 $kernel->bootstrap();
use Illuminate\Support\Facades\DB;

// Auto-make ALL columns nullable in students table
echo "=== Auto-fixing ALL student columns to nullable ===\n";
 $rows = DB::select("DESCRIBE students");
foreach($rows as $r) {
    if($r->Null == 'NO' && $r->Field != 'id' && $r->Key != 'PRI') {
        $col = $r->Field;
        $type = $r->Type;
        $def = $r->Default;
        $extra = $r->Extra;
        $default = '';
        if($def !== null) {
            $default = " DEFAULT '$def'";
        } elseif(strpos($type,'int') !== false || strpos($type,'bigint') !== false) {
            $default = " DEFAULT NULL";
        } elseif(strpos($type,'enum') !== false) {
            // Get first enum value as default
            preg_match("/enum\((.*)\)/", $type, $m);
            $vals = $m[1] ?? '';
            $first = explode(",",$vals)[0] ?? '';
            $default = " DEFAULT $first";
        } elseif(strpos($type,'timestamp') !== false) {
            continue; // skip timestamp columns
        } else {
            $default = " DEFAULT NULL";
        }
        $sql = "ALTER TABLE students MODIFY `$col` $type NULL$default $extra";
        try {
            DB::statement($sql);
            echo "  Fixed: $col ($type) -> NULL\n";
        } catch(\Exception $e) {
            echo "  Retry: $col -> " . substr($e->getMessage(),0,60) . "\n";
            // Try without default for timestamps etc
            try {
                DB::statement("ALTER TABLE students MODIFY `$col` $type NULL $extra");
                echo "  Fixed: $col (no default)\n";
            } catch(\Exception $e2) {
                echo "  SKIP: $col\n";
            }
        }
    }
}

// Now seed
echo "\n=== Seeding ===\n";
 $c = DB::table('students')->count();
if($c > 0) { echo "SKIP: Students ($c)\n"; exit; }

// Get all columns that actually exist
 $allCols = [];
foreach(DB::select("DESCRIBE students") as $r) $allCols[] = $r->Field;
echo "Columns: ".implode(", ",$allCols)."\n\n";

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
    
    // Build data with only existing columns
    $data = ['created_at'=>now(),'updated_at'=>now()];
    if(in_array('first_name',$allCols)) $data['first_name']=$n[0];
    if(in_array('last_name',$allCols)) $data['last_name']=$n[1];
    if(in_array('name',$allCols) && !in_array('first_name',$allCols)) $data['name']=$n[0].' '.$n[1];
    if(in_array('email',$allCols)) $data['email']=strtolower($n[0]).'.s@school.com';
    if(in_array('gender',$allCols)) $data['gender']=$n[2];
    if(in_array('date_of_birth',$allCols)) $data['date_of_birth']=$n[3];
    if(in_array('class_grade',$allCols)) $data['class_grade']=$cIds[$cId];
    if(in_array('section',$allCols)) $data['section']=($i%2==0)?'A':'B';
    if(in_array('roll_number',$allCols)) $data['roll_number']='SOR-'.str_pad($i+1,4,'0',STR_PAD_LEFT);
    if(in_array('address',$allCols)) $data['address']='Addis Ababa';
    if(in_array('status',$allCols)) $data['status']='active';
    if(in_array('admission_date',$allCols)) $data['admission_date']='2024-09-08';
    
    DB::table('students')->insert($data);
}
echo "OK: 20 Students seeded!\n";

// Settings
foreach([
    'school_name'=>'School of Redemption','school_slogan'=>'Where Dreams Take Flight',
    'school_phone'=>'+251-11-123-4567','school_email'=>'info@schoolofredemption.com',
    'school_address'=>'Addis Ababa, Bole Sub-City, Ethiopia',
] as $k => $v) {
    DB::table('settings')->updateOrInsert(['key'=>$k],['value'=>$v]);
}
echo "OK: Settings\n";
echo "\n=== DONE ===\n";
