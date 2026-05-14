<?php
echo "=== PROMOTION SYSTEM - Part A ===\n\n";
 $b = getcwd();
if (!file_exists($b."/artisan")) die("Run from Redemption root!\n");
function sw($p,$c){$d=dirname($p);if(!is_dir($d))mkdir($d,0777,true);file_put_contents($p,$c);echo "  ".str_replace(getcwd().'/','',$p)."\n";}

// Migrations
 $t1=date('Y_m_d_His');$t2=date('Y_m_d_His',time()+1);$t3=date('Y_m_d_His',time()+2);
sw($b."/database/migrations/{$t1}_create_grade_scales_table.php",'<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::create("grade_scales",function(Blueprint $t){$t->id();$t->string("name");$t->decimal("min_percentage",5,2);$t->decimal("max_percentage",5,2);$t->decimal("grade_point",3,2)->default(0);$t->text("description")->nullable();$t->boolean("is_passing")->default(true);$t->integer("sort_order")->default(0);$t->timestamps();});}
public function down():void{Schema::dropIfExists("grade_scales");}};');

sw($b."/database/migrations/{$t2}_create_promotion_settings_table.php",'<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::create("promotion_settings",function(Blueprint $t){$t->id();$t->foreignId("academic_year_id")->constrained()->cascadeOnDelete();$t->foreignId("class_id")->nullable()->constrained()->nullOnDelete();$t->decimal("minimum_average",5,2)->default(50.00);$t->integer("minimum_subjects_passed")->default(0);$t->boolean("core_subjects_must_pass")->default(true);$t->decimal("attendance_minimum",5,2)->default(75.00)->nullable();$t->boolean("include_attendance")->default(false);$t->decimal("behavior_minimum",5,2)->default(0)->nullable();$t->boolean("include_behavior")->default(false);$t->enum("promotion_type",["automatic","manual","hybrid"])->default("hybrid");$t->boolean("is_active")->default(true);$t->text("additional_rules")->nullable();$t->timestamps();$t->unique(["academic_year_id","class_id"],"promo_unique");});}
public function down():void{Schema::dropIfExists("promotion_settings");}};');

sw($b."/database/migrations/{$t3}_create_promotion_results_table.php",'<?php
use Illuminate\Database\Migrations\Migration;use Illuminate\Database\Schema\Blueprint;use Illuminate\Support\Facades\Schema;
return new class extends Migration{public function up():void{Schema::create("promotion_results",function(Blueprint $t){$t->id();$t->foreignId("student_id")->constrained()->cascadeOnDelete();$t->foreignId("academic_year_id")->constrained()->cascadeOnDelete();$t->foreignId("term_id")->nullable()->constrained()->nullOnDelete();$t->foreignId("from_class_id")->constrained("classes")->cascadeOnDelete();$t->foreignId("to_class_id")->nullable()->constrained("classes")->nullOnDelete();$t->enum("status",["promoted","detained","conditional","pending","review"])->default("pending");$t->decimal("overall_average",5,2)->default(0);$t->decimal("overall_percentage",5,2)->default(0);$t->string("overall_grade")->nullable();$t->decimal("grade_point_average",3,2)->default(0);$t->integer("total_subjects")->default(0);$t->integer("subjects_passed")->default(0);$t->integer("subjects_failed")->default(0);$t->integer("class_rank")->nullable();$t->integer("total_students")->nullable();$t->decimal("attendance_percentage",5,2)->nullable();$t->text("failure_reasons")->nullable();$t->text("remarks")->nullable();$t->foreignId("processed_by")->nullable()->constrained("users")->nullOnDelete();$t->timestamp("processed_at")->nullable();$t->boolean("is_final")->default(false);$t->timestamps();$t->unique(["student_id","academic_year_id","term_id"],"promo_result_unique");});}
public function down():void{Schema::dropIfExists("promotion_results");}};');

echo "\nRunning migrations...\n";
exec("php artisan migrate --force 2>&1",$mo);echo implode("\n",$mo)."\n";

// Seed grade scales
echo "\nSeeding grades...\n";
 $env=file_get_contents($b."/.env");preg_match("/DB_DATABASE=(.*)/",$env,$m1);preg_match("/DB_USERNAME=(.*)/",$env,$m2);preg_match("/DB_PASSWORD=(.*)/",$env,$m3);preg_match("/DB_HOST=(.*)/",$env,$m4);
 $dn=isset($m1[1])?trim($m1[1]):"redemption";$du=isset($m2[1])?trim($m2[1]):"root";$dp=isset($m3[1])?trim($m3[1]):"";$dh=isset($m4[1])?trim($m4[1]):"127.0.0.1";
try{$db=new PDO("mysql:host=$dh;dbname=$dn",$du,$dp);$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);}catch(Exception $e){die("DB Error: ".$e->getMessage()."\n");}
 $c=$db->query("SELECT COUNT(*) FROM grade_scales")->fetchColumn();
if($c==0){
    $gs=[["A+",95,100,4.00,"Outstanding",1],["A",90,94.99,4,"Excellent",2],["A-",85,89.99,3.75,"Very Good",3],["B+",80,84.99,3.5,"Good",4],["B",75,79.99,3,"Above Average",5],["B-",70,74.99,2.75,"Satisfactory",6],["C+",65,69.99,2.5,"Average",7],["C",60,64.99,2,"Below Average",8],["C-",55,59.99,1.75,"Poor",9],["D",50,54.99,1,"Marginal Pass",10],["F",0,49.99,0,"Fail",11]];
    $sql="INSERT INTO grade_scales (name,min_percentage,max_percentage,grade_point,description,is_passing,sort_order,created_at,updated_at) VALUES ";
    $v=[];foreach($gs as $g)$v[]="('$g[0]',$g[1],$g[2],$g[3],'$g[4]',".($g[5]>0?1:0).",$g[5],NOW(),NOW())";
    $db->exec($sql.implode(",",$v));echo "  Inserted ".count($gs)." grades\n";
}else echo "  Already exist ($c)\n";

// Models
sw($b."/app/Models/GradeScale.php",'<?php
namespace App\Models;use Illuminate\Database\Eloquent\Factories\HasFactory;use Illuminate\Database\Eloquent\Model;
class GradeScale extends Model{use HasFactory;
protected $fillable=["name","min_percentage","max_percentage","grade_point","description","is_passing","sort_order"];
protected $casts=["min_percentage"=>"decimal:2","max_percentage"=>"decimal:2","grade_point"=>"decimal:2","is_passing"=>"boolean"];
public static function getGrade(float $p):?self{return static::where("min_percentage","<=",$p)->where("max_percentage",">=",$p)->orderBy("sort_order")->first();}
public static function getGradeName(float $p):string{$g=static::getGrade($p);return $g?$g->name:"N/A";}
public static function isPassing(float $p):bool{$g=static::getGrade($p);return $g?$g->is_passing:false;}
public static function getGradePoint(float $p):float{$g=static::getGrade($p);return $g?(float)$g->grade_point:0;}}');

sw($b."/app/Models/PromotionSetting.php",'<?php
namespace App\Models;use Illuminate\Database\Eloquent\Factories\HasFactory;use Illuminate\Database\Eloquent\Model;
class PromotionSetting extends Model{use HasFactory;
protected $fillable=["academic_year_id","class_id","minimum_average","minimum_subjects_passed","core_subjects_must_pass","attendance_minimum","include_attendance","behavior_minimum","include_behavior","promotion_type","is_active","additional_rules"];
protected $casts=["minimum_average"=>"decimal:2","minimum_subjects_passed"=>"integer","core_subjects_must_pass"=>"boolean","attendance_minimum"=>"decimal:2","include_attendance"=>"boolean","behavior_minimum"=>"decimal:2","include_behavior"=>"boolean","is_active"=>"boolean"];
public function academicYear(){return $this->belongsTo(AcademicYear::class);}
public function classroom(){return $this->belongsTo(ClassRoom::class,"class_id");}
public static function getSettings(int $a,?int $c=null):?self{if($c){$s=static::where("academic_year_id",$a)->where("class_id",$c)->where("is_active",true)->first();if($s)return $s;}return static::where("academic_year_id",$a)->whereNull("class_id")->where("is_active",true)->first();}}');

sw($b."/app/Models/PromotionResult.php",'<?php
namespace App\Models;use Illuminate\Database\Eloquent\Factories\HasFactory;use Illuminate\Database\Eloquent\Model;
class PromotionResult extends Model{use HasFactory;
protected $fillable=["student_id","academic_year_id","term_id","from_class_id","to_class_id","status","overall_average","overall_percentage","overall_grade","grade_point_average","total_subjects","subjects_passed","subjects_failed","class_rank","total_students","attendance_percentage","failure_reasons","remarks","processed_by","processed_at","is_final"];
protected $casts=["overall_average"=>"decimal:2","overall_percentage"=>"decimal:2","grade_point_average"=>"decimal:2","total_subjects"=>"integer","subjects_passed"=>"integer","subjects_failed"=>"integer","class_rank"=>"integer","total_students"=>"integer","attendance_percentage"=>"decimal:2","is_final"=>"boolean","processed_at"=>"datetime"];
public function student(){return $this->belongsTo(Student::class);}
public function academicYear(){return $this->belongsTo(AcademicYear::class);}
public function term(){return $this->belongsTo(Term::class);}
public function fromClass(){return $this->belongsTo(ClassRoom::class,"from_class_id");}
public function toClass(){return $this->belongsTo(ClassRoom::class,"to_class_id");}
public function processedBy(){return $this->belongsTo(User::class,"processed_by");}
public function getStatusBadgeAttribute(){$b=["promoted"=>"<span class=\"badge bg-success\">Promoted</span>","detained"=>"<span class=\"badge bg-danger\">Detained</span>","conditional"=>"<span class=\"badge bg-warning text-dark\">Conditional</span>","pending"=>"<span class=\"badge bg-secondary\">Pending</span>","review"=>"<span class=\"badge bg-info\">Review</span>"];return $b[$this->status]??$this->status;}
public function getStatusColorAttribute(){$c=["promoted"=>"success","detained"=>"danger","conditional"=>"warning","pending"=>"secondary","review"=>"info"];return $c[$this->status]??"secondary";}}');

echo "\nPart A complete. Now run Part B.\n";
