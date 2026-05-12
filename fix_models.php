<?php
echo "=== Fix Model Relationships ===\n\n";
 $b = __DIR__;

// FEE MODEL
 $f = file_get_contents($b.'/app/Models/Fee.php');
if(strpos($f, 'function classroom') === false){
    $f = str_replace('class Fee extends Model', 'class Fee extends Model
{
    public function classroom(){ return $this->belongsTo(Classroom::class, "class_id"); }
    public function academicYear(){ return $this->belongsTo(AcademicYear::class, "academic_year_id"); }', $f);
    if(strpos($f, 'use App\Models\Classroom') === false){
        $f = str_replace('namespace App\Models;', "namespace App\Models;\nuse App\Models\Classroom;\nuse App\Models\AcademicYear;", $f);
    }
    file_put_contents($b.'/app/Models/Fee.php', $f);
    echo "[OK] Fee model\n";
} else { echo "[SKIP] Fee model already has relationships\n"; }

// FEE PAYMENT MODEL
 $fp = file_get_contents($b.'/app/Models/FeePayment.php');
if(strpos($fp, 'function fee') === false){
    $fp = str_replace('class FeePayment extends Model', 'class FeePayment extends Model
{
    public function fee(){ return $this->belongsTo(Fee::class, "fee_id"); }
    public function student(){ return $this->belongsTo(Student::class, "student_id"); }', $fp);
    if(strpos($fp, 'use App\Models\Fee') === false){
        $fp = str_replace('namespace App\Models;', "namespace App\Models;\nuse App\Models\Fee;\nuse App\Models\Student;", $fp);
    }
    file_put_contents($b.'/app/Models/FeePayment.php', $fp);
    echo "[OK] FeePayment model\n";
} else { echo "[SKIP] FeePayment model already has relationships\n"; }

// PROGRESS REPORT MODEL
 $pr = file_get_contents($b.'/app/Models/ProgressReport.php');
if(strpos($pr, 'function student') === false){
    $pr = str_replace('class ProgressReport extends Model', 'class ProgressReport extends Model
{
    public function student(){ return $this->belongsTo(Student::class, "student_id"); }
    public function academicYear(){ return $this->belongsTo(AcademicYear::class, "academic_year_id"); }
    public function term(){ return $this->belongsTo(Term::class, "term_id"); }
    public function classroom(){ return $this->belongsTo(Classroom::class, "class_id"); }', $pr);
    if(strpos($pr, 'use App\Models\Student') === false){
        $pr = str_replace('namespace App\Models;', "namespace App\Models;\nuse App\Models\Student;\nuse App\Models\AcademicYear;\nuse App\Models\Term;\nuse App\Models\Classroom;", $pr);
    }
    file_put_contents($b.'/app/Models/ProgressReport.php', $pr);
    echo "[OK] ProgressReport model\n";
} else { echo "[SKIP] ProgressReport model already has relationships\n"; }

// Clear caches
foreach(['view:clear','config:clear','cache:clear','route:clear'] as $cmd){
    $o=shell_exec('php artisan '.$cmd.' 2>&1'); echo trim($o)."\n";
}
echo "\n=== All models updated! ===\n";
echo "Modules implemented: Parents, Fee Structure, Fee Payments, Settings, Progress Reports\n";
echo "All styled in Subject page style with proper forms, dropdowns, and validation.\n";
