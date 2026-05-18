<?php
$base = dirname(__DIR__);
$ctrlFile = $base . '/app/Http/Controllers/Admin/MarkEntryController.php';
$code = file_get_contents($ctrlFile);
if (!$code) { echo "ERROR: Cannot read controller"; exit; }
$newMethod = <<<'PHP'
    public function apiLoadStudents(Request $request) {
        $ayId=$request->query('academic_year_id'); $termId=$request->query('term_id');
        $classId=$request->query('class_id'); $sectionId=$request->query('section_id'); $subjectId=$request->query('subject_id');
        if (!$ayId||!$termId||!$classId||!$sectionId||!$subjectId) return response()->json(['error'=>'All filters required'],400);
        $query = DB::table('students')
            ->whereNull('deleted_at')
            ->where('status','active')
            ->where('class_id',$classId)
            ->where('section_id',$sectionId)
            ->where('academic_year_id',$ayId)
            ->orderBy('first_name')->orderBy('last_name')
            ->select('id as student_id','first_name','middle_name','last_name','roll_number');
        $students = $query->get();
        $existingMarks = MarkEntry::where('academic_year_id',$ayId)->where('term_id',$termId)
            ->where('class_id',$classId)->where('section_id',$sectionId)->where('subject_id',$subjectId)
            ->get()->keyBy('student_id');
        $markFields = MarkEntry::getMarkFields();
        $rows = [];
        foreach ($students as $s) {
            $fullName = trim(($s->first_name??'').' '.($s->middle_name??'').' '.($s->last_name??''));
            $mark = $existingMarks->get($s->student_id);
            $row = ['student_id'=>$s->student_id,'student_name'=>$fullName?:'Unknown','roll_number'=>$s->roll_number];
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
$pattern = '/public function apiLoadStudents\s*\.s*?\{.*?\n    \}/s';
if (preg_match($pattern, $code)) {
    $code = preg_replace($pattern, $newMethod, $code, 1);
    file_put_contents($ctrlFile, $code);
    echo "OK - replaced (regex)\n";
} else {
    $startPos = strpos($code, 'public function apiLoadStudents(Request $request)');
    if ($startPos === false) { echo "FAIL - not found\n"; exit; }
    $braceCount = 0; $started = false;
    for ($i = $startPos; $i < strlen($code); $i++) {
        if ($code[$i] === '{') { $braceCount++; $started = true; }
        if ($code[$i] === '}') { $braceCount--; }
        if ($started && $braceCount === 0) { $endPos = $i + 1; break; }
    }
    $code = substr($code, 0, $startPos) . $newMethod . substr($code, $endPos);
    file_put_contents($ctrlFile, $code);
    echo "OK - replaced (bracket)\n";
}
try {
    DB::statement('ALTER TABLE mark_entries MODIFY COLUMN exam_id BIGINT UNSIGNED NULL');
    echo "OK - exam_id nullable\n";
} catch (Exception $e) {
    echo "WARN : " . $e->getMessage() . "\n";
}
$baseDir = dirname(__DIR__);
foreach (['view:clear','cache:clear','route:clear'] as $c) {
    echo trim(shell_exec('cd '.escapeshellarg($baseDir).' && php artisan '.$c.' 2>&1'))."\n";
}
$check = file_get_contents($ctrlFile);
echo "Verify: students.class_id=".p(strpos($check,"students.class_id")!==false?'YES':'NO').", enrollments=".p(strpos($check,"student_enrollments")===false?'YES':'NO')."\nDONE\n";