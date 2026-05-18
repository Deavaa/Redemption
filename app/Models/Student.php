<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','branch_id','class_id','section_id','academic_year_id','first_name','last_name','email','phone','address','guardian_name','guardian_phone','roll_number','admission_number','admission_date','date_of_birth','gender','blood_group','religion','nationality','previous_school','ethnicity','place_of_birth','passport_number','medical_conditions','allergies','notes','teacher_comments','admin_comments','photo','status'];
    protected function casts(): array { return ['admission_date'=>'date','date_of_birth'=>'date']; }
    public function user() { return $this->belongsTo(User::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function classroom() { return $this->belongsTo(Classroom::class, 'class_id'); }
    public function section() { return $this->belongsTo(Section::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function parents() { return $this->belongsToMany(ParentModel::class, 'student_parent', 'student_id', 'parent_id')->withPivot('relation')->withTimestamps(); }
    public function markEntries() { return $this->hasMany(MarkEntry::class); }
    public function certificates() { return $this->hasMany(Certificate::class); }
    public function idCards() { return $this->hasMany(IdCard::class); }
    public function progressReports() { return $this->hasMany(ProgressReport::class); }
    public function performanceReports() { return $this->hasMany(PerformanceReport::class); }
    public function feePayments() { return $this->hasMany(FeePayment::class); }
    public function attendances() { return $this->hasMany(Attendance::class); }
}