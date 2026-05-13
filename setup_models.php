<?php

echo "=== Creating Models ===\n\n";

require __DIR__ . '/vendor/autoload.php';
 $app = require_once __DIR__ . '/bootstrap/app.php';
 $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
 $kernel->bootstrap();

 $dir = app_path('Models');
if (!is_dir($dir)) mkdir($dir, 0755, true);

function mk($name, $code) {
    global $dir;
    file_put_contents("$dir/$name.php", $code);
    echo "  [OK] $name\n";
}


mk('User', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;
    protected $fillable = ['name','email','password','role','phone','address','profile_photo','is_active'];
    protected $hidden = ['password','remember_token'];
    protected function casts(): array { return ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean']; }
    public function student() { return $this->hasOne(Student::class); }
    public function parent() { return $this->hasOne(ParentModel::class); }
    public function teacherAssignments() { return $this->hasMany(TeacherAssignment::class, 'teacher_id'); }
    public function employeeAssets() { return $this->hasMany(EmployeeAsset::class, 'employee_id'); }
    public function leaves() { return $this->hasMany(Leave::class, 'employee_id'); }
    public function payrolls() { return $this->hasMany(Payroll::class, 'employee_id'); }
    public function approvedLeaves() { return $this->hasMany(Leave::class, 'approved_by'); }
    public function classes() { return $this->hasMany(Classroom::class, 'teacher_id'); }
    public function isAdmin() { return $this->role === 'admin'; }
    public function isTeacher() { return $this->role === 'teacher'; }
}
P);

mk('Branch', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $fillable = ['name','address','phone','email','gps_lat','gps_lng','is_active'];
    protected function casts(): array { return ['gps_lat'=>'decimal:8','gps_lng'=>'decimal:8','is_active'=>'boolean']; }
    public function classes() { return $this->hasMany(Classroom::class); }
    public function students() { return $this->hasMany(Student::class); }
    public function incomeExpenses() { return $this->hasMany(IncomeExpense::class); }
    public function contactMessages() { return $this->hasMany(ContactMessage::class); }
}
P);

mk('AcademicYear', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;
    protected $fillable = ['name','start_date','end_date','is_current'];
    protected function casts(): array { return ['start_date'=>'date','end_date'=>'date','is_current'=>'boolean']; }
    public function terms() { return $this->hasMany(Term::class); }
    public function classes() { return $this->hasMany(Classroom::class); }
    public function students() { return $this->hasMany(Student::class); }
    public function exams() { return $this->hasMany(Exam::class); }
    public function teacherAssignments() { return $this->hasMany(TeacherAssignment::class); }
    public function fees() { return $this->hasMany(Fee::class); }
    public function budgets() { return $this->hasMany(Budget::class); }
    public function incomeExpenses() { return $this->hasMany(IncomeExpense::class); }
    public function progressReports() { return $this->hasMany(ProgressReport::class); }
    public function performanceReports() { return $this->hasMany(PerformanceReport::class); }
    public function audits() { return $this->hasMany(Audit::class); }
    public function financeStatements() { return $this->hasMany(FinanceStatement::class); }
}
P);

mk('Term', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;
    protected $fillable = ['academic_year_id','name','start_date','end_date','is_active'];
    protected function casts(): array { return ['start_date'=>'date','end_date'=>'date','is_active'=>'boolean']; }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function exams() { return $this->hasMany(Exam::class); }
    public function progressReports() { return $this->hasMany(ProgressReport::class); }
    public function performanceReports() { return $this->hasMany(PerformanceReport::class); }
}
P);

mk('Classroom', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;
    protected $table = 'classes';
    protected $fillable = ['branch_id','academic_year_id','name','numeric_name','teacher_id'];
    public function branch() { return $this->belongsTo(Branch::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function sections() { return $this->hasMany(Section::class); }
    public function students() { return $this->hasMany(Student::class); }
    public function exams() { return $this->hasMany(Exam::class); }
    public function fees() { return $this->hasMany(Fee::class); }
    public function classAssets() { return $this->hasMany(ClassAsset::class); }
    public function progressReports() { return $this->hasMany(ProgressReport::class); }
    public function teacherAssignments() { return $this->hasMany(TeacherAssignment::class); }
}
P);

mk('Section', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;
    protected $fillable = ['class_id','name','capacity'];
    public function classroom() { return $this->belongsTo(Classroom::class, 'class_id'); }
    public function students() { return $this->hasMany(Student::class); }
    public function teacherAssignments() { return $this->hasMany(TeacherAssignment::class); }
}
P);

mk('Subject', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;
    protected $fillable = ['name','code','type','description'];
    public function teacherAssignments() { return $this->hasMany(TeacherAssignment::class); }
    public function exams() { return $this->hasMany(Exam::class); }
}
P);

mk('Student', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','branch_id','class_id','section_id','academic_year_id','roll_number','admission_number','admission_date','date_of_birth','gender','blood_group','religion','nationality','previous_school'];
    protected function casts(): array { return ['admission_date'=>'date','date_of_birth'=>'date']; }
    public function user() { return $this->belongsTo(User::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function classroom() { return $this->belongsTo(Classroom::class, 'class_id'); }
    public function section() { return $this->belongsTo(Section::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function parents() { return $this->belongsToMany(ParentModel::class, 'student_parent')->withPivot('relation')->withTimestamps(); }
    public function markEntries() { return $this->hasMany(MarkEntry::class); }
    public function certificates() { return $this->hasMany(Certificate::class); }
    public function idCards() { return $this->hasMany(IdCard::class); }
    public function progressReports() { return $this->hasMany(ProgressReport::class); }
    public function performanceReports() { return $this->hasMany(PerformanceReport::class); }
    public function feePayments() { return $this->hasMany(FeePayment::class); }
}
P);

mk('ParentModel', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentModel extends Model
{
    use HasFactory;
    protected $table = 'parents';
    protected $fillable = ['user_id','father_name','mother_name','father_occupation','mother_occupation','father_phone','mother_phone','guardian_name','guardian_relation','guardian_phone'];
    public function user() { return $this->belongsTo(User::class); }
    public function students() { return $this->belongsToMany(Student::class, 'student_parent')->withPivot('relation')->withTimestamps(); }
}
P);

mk('TeacherAssignment', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAssignment extends Model
{
    use HasFactory;
    protected $fillable = ['teacher_id','class_id','section_id','subject_id','academic_year_id'];
    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
    public function classroom() { return $this->belongsTo(Classroom::class, 'class_id'); }
    public function section() { return $this->belongsTo(Section::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
}
P);

mk('Exam', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;
    protected $fillable = ['academic_year_id','term_id','class_id','subject_id','name','type','total_marks','passing_marks','exam_date'];
    protected function casts(): array { return ['total_marks'=>'decimal:2','passing_marks'=>'decimal:2','exam_date'=>'date']; }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function term() { return $this->belongsTo(Term::class); }
    public function classroom() { return $this->belongsTo(Classroom::class, 'class_id'); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function markEntries() { return $this->hasMany(MarkEntry::class); }
}
P);

mk('MarkEntry', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkEntry extends Model
{
    use HasFactory;
    protected $fillable = ['exam_id','student_id','marks_obtained','grade','remarks'];
    protected function casts(): array { return ['marks_obtained'=>'decimal:2']; }
    public function exam() { return $this->belongsTo(Exam::class); }
    public function student() { return $this->belongsTo(Student::class); }
}

mk('Certificate', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;
    protected $fillable = ['student_id','type','certificate_number','issue_date','content','template'];
    protected function casts(): array { return ['issue_date'=>'date']; }
    public function student() { return $this->belongsTo(Student::class); }
}
P);

mk('IdCard', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdCard extends Model
{
    use HasFactory;
    protected $fillable = ['student_id','card_number','issue_date','valid_until','status'];
    protected function casts(): array { return ['issue_date'=>'date','valid_until'=>'date']; }
    public function student() { return $this->belongsTo(Student::class); }
}
P);

mk('ProgressReport', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressReport extends Model
{
    use HasFactory;
    protected $fillable = ['student_id','academic_year_id','term_id','class_id','total_marks','percentage','grade','rank','remarks','teacher_comment'];
    protected function casts(): array { return ['total_marks'=>'decimal:2','percentage'=>'decimal:2']; }
    public function student() { return $this->belongsTo(Student::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function term() { return $this->belongsTo(Term::class); }
    public function classroom() { return $this->belongsTo(Classroom::class, 'class_id'); }
}
P);

mk('PerformanceReport', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceReport extends Model
{
    use HasFactory;
    protected $fillable = ['student_id','academic_year_id','term_id','attendance_percentage','behavior_rating','sports_rating','extracurricular_rating','overall_rating','remarks'];
    protected function casts(): array { return ['attendance_percentage'=>'decimal:2']; }
    public function student() { return $this->belongsTo(Student::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function term() { return $this->belongsTo(Term::class); }
}
P);

mk('ClassAsset', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassAsset extends Model
{
    use HasFactory;
    protected $fillable = ['class_id','name','quantity','condition','purchase_date','purchase_price','description'];
    protected function casts(): array { return ['purchase_date'=>'date','purchase_price'=>'decimal:2']; }
    public function classroom() { return $this->belongsTo(Classroom::class, 'class_id'); }
}
P);

mk('EmployeeAsset', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAsset extends Model
{
    use HasFactory;
    protected $fillable = ['employee_id','name','quantity','condition','issue_date','return_date','description'];
    protected function casts(): array { return ['issue_date'=>'date','return_date'=>'date']; }
    public function employee() { return $this->belongsTo(User::class, 'employee_id'); }
}
P);

mk('Fee', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    use HasFactory;
    protected $fillable = ['class_id','academic_year_id','fee_type','amount','due_date','description','is_active'];
    protected function casts(): array { return ['amount'=>'decimal:2','due_date'=>'date','is_active'=>'boolean']; }
    public function classroom() { return $this->belongsTo(Classroom::class, 'class_id'); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function feePayments() { return $this->hasMany(FeePayment::class); }
}
P);

mk('FeePayment', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    use HasFactory;
    protected $fillable = ['fee_id','student_id','amount_paid','payment_date','payment_method','transaction_id','receipt_number','status'];
    protected function casts(): array { return ['amount_paid'=>'decimal:2','payment_date'=>'date']; }
    public function fee() { return $this->belongsTo(Fee::class); }
    public function student() { return $this->belongsTo(Student::class); }
}
P);

mk('Leave', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;
    protected $fillable = ['employee_id','leave_type','start_date','end_date','total_days','reason','status','approved_by'];
    protected function casts(): array { return ['start_date'=>'date','end_date'=>'date']; }
    public function employee() { return $this->belongsTo(User::class, 'employee_id'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
P);

mk('Payroll', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;
    protected $fillable = ['employee_id','basic_salary','allowances','deductions','tax','net_salary','pay_period','payment_date','status'];
    protected function casts(): array { return ['basic_salary'=>'decimal:2','allowances'=>'decimal:2','deductions'=>'decimal:2','tax'=>'decimal:2','net_salary'=>'decimal:2','payment_date'=>'date']; }
    public function employee() { return $this->belongsTo(User::class, 'employee_id'); }
}
P);

mk('Budget', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;
    protected $fillable = ['academic_year_id','category','allocated_amount','spent_amount','description','status'];
    protected function casts(): array { return ['allocated_amount'=>'decimal:2','spent_amount'=>'decimal:2']; }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
}
P);

mk('IncomeExpense', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomeExpense extends Model
{
    use HasFactory;
    protected $fillable = ['academic_year_id','type','category','amount','date','description','reference','branch_id'];
    protected function casts(): array { return ['amount'=>'decimal:2','date'=>'date']; }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
}
P);

mk('FinanceStatement', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceStatement extends Model
{
    use HasFactory;
    protected $fillable = ['academic_year_id','branch_id','statement_type','period_from','period_to','total_income','total_expense','net_balance','description'];
    protected function casts(): array { return ['period_from'=>'date','period_to'=>'date','total_income'=>'decimal:2','total_expense'=>'decimal:2','net_balance'=>'decimal:2']; }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
}
P);

mk('Audit', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasFactory;
    protected $fillable = ['academic_year_id','branch_id','auditor_name','audit_date','findings','recommendations','status'];
    protected function casts(): array { return ['audit_date'=>'date']; }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
}
P);

mk('TeamMember', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;
    protected $fillable = ['name','designation','department','qualification','experience','phone','email','photo','bio','sort_order','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
}
P);

mk('GalleryImage', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryImage extends Model
{
    use HasFactory;
    protected $fillable = ['title','description','image_path','category','sort_order','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
}
P);

mk('GalleryVideo', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GalleryVideo extends Model
{
    use HasFactory;
    protected $fillable = ['title','description','video_url','thumbnail','sort_order','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
}
P);

mk('Slider', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;
    protected $fillable = ['title','subtitle','image_path','link','sort_order','is_active'];
    protected function casts(): array { return ['is_active'=>'boolean']; }
}
P);

mk('Setting', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $fillable = ['key','value','group','type','description'];
    public static function get($key, $default = null) {
        $s = static::where('key', $key)->first();
        return $s ? $s->value : $default;
    }
    public static function set($key, $value) {
        return static::updateOrCreate(['key'=>$key], ['value'=>$value]);
    }
}
P);

mk('ContactMessage', <<<'P'
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;
    protected $fillable = ['name','email','phone','subject','message','branch_id','is_read'];
    protected function casts(): array { return ['is_read'=>'boolean']; }
    public function branch() { return $this->belongsTo(Branch::class); }
}
P);

echo "\n=== DONE! 33 models created ===\n";
