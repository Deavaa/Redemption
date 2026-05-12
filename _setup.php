<?php
/**
 * School of Redemption - Master Setup
 * Run: php _setup.php
 */
 $base = __DIR__;
function wf($p,$c){$d=dirname($p);if(!is_dir($d))mkdir($d,0755,true);file_put_contents($p,$c);echo "  OK: ".basename($p)."\n";}

echo "=== School of Redemption Setup ===\n\n";

// ===== MODELS =====
echo "[1] Models...\n";
wf("$base/app/Models/ClassRoom.php",'<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ClassRoom extends Model{
    use HasFactory;
    protected $table="classes";
    protected $fillable=["branch_id","academic_year_id","name","numeric_name","teacher_id"];
    public function sections(){return $this->hasMany(Section::class,"class_id");}
    public function academicYear(){return $this->belongsTo(AcademicYear::class);}
}');

wf("$base/app/Models/Section.php",'<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Section extends Model{
    use HasFactory;
    protected $fillable=["class_id","name","max_students","teacher_id","capacity"];
    public function classRoom(){return $this->belongsTo(ClassRoom::class,"class_id");}
}');

wf("$base/app/Models/Subject.php",'<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Subject extends Model{
    use HasFactory;
    protected $fillable=["name","code","type","description"];
}');

wf("$base/app/Models/TeacherAssignment.php",'<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class TeacherAssignment extends Model{
    use HasFactory;
    protected $fillable=["teacher_id","class_id","section_id","subject_id","academic_year_id"];
    public function subject(){return $this->belongsTo(Subject::class);}
    public function classRoom(){return $this->belongsTo(ClassRoom::class,"class_id");}
    public function section(){return $this->belongsTo(Section::class);}
    public function teacher(){return $this->belongsTo(User::class,"teacher_id");}
    public function academicYear(){return $this->belongsTo(AcademicYear::class);}
}');

wf("$base/app/Models/MarkEntry.php",'<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class MarkEntry extends Model{
    use HasFactory;
    protected $table="mark_entries";
    protected $fillable=["student_id","subject_id","academic_year_id","term_id","class_id","section_id","teacher_id","ca1","ca2","ca3","ca4","ca5","ca6","ca7","ca8","ca9","ca10","conduct","handwriting","creativity","test1","test2","mid_term","final_exam","ca_total","exam_total","grand_total"];
    public static function getMarkFields(){return [["col"=>"ca1","max"=>5],["col"=>"ca2","max"=>5],["col"=>"ca3","max"=>5],["col"=>"ca4","max"=>5],["col"=>"ca5","max"=>5],["col"=>"ca6","max"=>5],["col"=>"ca7","max"=>5],["col"=>"ca8","max"=>5],["col"=>"ca9","max"=>5],["col"=>"ca10","max"=>5],["col"=>"conduct","max"=>5],["col"=>"handwriting","max"=>5],["col"=>"creativity","max"=>10],["col"=>"test1","max"=>10],["col"=>"test2","max"=>10],["col"=>"mid_term","max"=>20],["col"=>"final_exam","max"=>30]];}
    public static function calcTotals(array $d):array{$caF=["ca1","ca2","ca3","ca4","ca5","ca6","ca7","ca8","ca9","ca10","conduct","handwriting","creativity"];$exF=["test1","test2","mid_term","final_exam"];$cr=0;foreach($caF as $f)$cr+=floatval($d[$f]??0);$er=0;foreach($exF as $f)$er+=floatval($d[$f]??0);$d["ca_total"]=round(($cr/70)*30,2);$d["exam_total"]=min($er,70);$d["grand_total"]=round($d["ca_total"]+$d["exam_total"],2);return $d;}
}');

wf("$base/app/Models/Exam.php",'<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Exam extends Model{
    use HasFactory;
    protected $fillable=["academic_year_id","term_id","name","type","start_date","end_date","start_time","end_time","total_marks","passing_marks","exam_date","description","class_id","subject_id"];
    protected $casts=["start_date"=>"date","end_date"=>"date","exam_date"=>"date"];
    public function academicYear(){return $this->belongsTo(AcademicYear::class);}
    public function term(){return $this->belongsTo(Term::class);}
}');

echo "\nDONE! Models created.\n";
echo "Now run: php artisan route:clear && php artisan view:clear\n";
echo "Then test Mark Entry page.\n";
