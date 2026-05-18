<?php
$base = dirname(__DIR__);
$ctrlFile = $base . '/app/Http/Controllers/Admin/MarkEntryController.php';

// Read existing controller
$code = file_get_contents($ctrlFile);
if (!$code) { echo "ERROR: Cannot read controller file"; exit; }

// New apiLoadStudents method
$newMethod = <<<'PHP'
    public function apiLoadStudents(Request $request) {
        $ayId=$request->query('academic_year_id'); $termId=$request->query('term_id');
        $classId=$request->query('class_id'); $sectionId=$request->query('section_id'); $subjectId=$request->query('subject_id');
        if (!$ayId||!$termId||!$classId||!$sectionId||!$subjectId) return response()->json(['error'=>'All filters required'],400);

        // Find enrolled students via student_enrollments table
        $enrolledIds = DB::table('student_enrollments')
            ->where('class_id',$classId)->where('section_id',$sectionId)
            ->where('academic_year_id',$ayId)->where('status','enrolled')
            ->pluck('student_id');

        // Get student names from students table (first_name, middle_name, last_name)
        $students = DB::table('students')
            ->whereIn('id', $enrolledIds)->whereNull('deleted_at')
            ->orderBy('first_name')->orderBy('last_name')
            ->select('id as student_id','first_name','middle_name','last_name')->get();

        // Get roll numbers from enrollments
        $enrollments = DB::table('student_enrollments')
            ->where('class_id',$classId)->where('section_id',$sectionId)
            ->where('academic_year_id',$ayId)->where('status','enrolled')
            ->get()->keyBy('student_id');

        // Get existing marks
        $existingMarks = MarkEntry::where('academic_year_id',$ayId)->where('term_id',$termId)
            ->where('class_id',$classId)->where('section_id',$sectionId)->where('subject_id',$subjectId)
            ->get()->keyBy('student_id');

        $markFields = MarkEntry::getMarkFields();
        $rows = [];

        foreach ($students as $s) {
            $fullName = trim(($s->first_name??'').' '.($s->middle_name??'').' '.($s->last_name??''));
            $enr = $enrollments->get($s->student_id);
            $mark = $existingMarks->get($s->student_id);
            $row = [
                'student_id'=>$s->student_id,
                'student_name'=>$fullName?:'Unknown',
                'roll_number'=>$enr?$enr->roll_number:null,
            ];
            foreach ($markFields as $field) { $col=$field['col']; $row[$col]=$mark?floatval($mark->$col):null; }
            $row['ca_total']=$mark?floatval($mark->ca_total):0;
            $row['exam_total']=$mark?floatval($mark->exam_total):0;
            $row['grand_total']=$mark?floatval($mark->grand_total):0;
            $rows[]=$row;
        }

        $subject=Subject::find($subjectId); $term=Term::find($termId); $class=ClassRoom::find($classId); $section=Section::find($sectionId);
        return response()->json(['students'=>$rows,'markFields'=>$markFields,
            'subject'=>$subject?$subject->name:'','term'=>$term?$term->name:'',
            'class'=>$class?$class->name:'','section'=>$section?$section->name:'']);
    }
PHP;

// Replace old apiLoadStudents method
$pattern = '/public function apiLoadStudents\s*\(.*?\{.*?\n    \}/s';
if (preg_match($pattern, $code)) {
    $code = preg_replace($pattern, $newMethod, $code, 1);
    file_put_contents($ctrlFile, $code);
    echo "OK - apiLoadStudents method replaced in controller\n";
} else {
    $startPos = strpos($code, 'public function apiLoadStudents(Request $request)');
    if ($startPos === false) { echo "FAIL - Could not find method\n"; exit; }
    $braceCount = 0; $started = false; $endPos = $startPos;
    for ($i = $startPos; $i < strlen($code); $i++) {
        if ($code[$i] === '{') { $braceCount++; $started = true; }
        if ($code[$i] === '}') { $braceCount--; }
        if ($started && $braceCount === 0) { $endPos = $i + 1; break; }
    }
    $code = substr($code, 0, $startPos) . $newMethod . substr($code, $endPos);
    file_put_contents($ctrlFile, $code);
    echo "OK - method replaced (fallback)\n";
}

$baseDir = dirname(__DIR__);
foreach (['view:clear','cache:clear','route:clear','config:clear'] as $c) {
    $out = shell_exec('cd '.escapeshellarg($baseDir).' && php artisan '.$c.' 2>&1');
    echo trim($out)."\n";
}

$check = file_get_contents($ctrlFile);
$has1 = strpos($check, 'first_name') !== false;
$has2 = strpos($check, 'student_enrollments') !== false;
echo "Verify: first_name=".($has1?'YES':'NO').", enrollments=".($has2?'YES':'NO')."\n";
echo ($has1&&$has2) ? "SUCCESS!" : "FAILED";
