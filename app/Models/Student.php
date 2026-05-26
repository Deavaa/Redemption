<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','branch_id','class_id','section_id','academic_year_id','full_name','email','phone','address','guardian_name','guardian_phone','roll_number','admission_number','admission_date','date_of_birth','gender','blood_group','religion','nationality','previous_school','ethnicity','place_of_birth','passport_number','medical_conditions','allergies','notes','teacher_comments','admin_comments','photo','status','original_admission_date','readmission_count','previous_branch_id','previous_class_id','previous_section_id','leave_date','leave_reason','is_readmitted'];
    protected $appends = [];
    protected function casts(): array { return ['admission_date'=>'date','date_of_birth'=>'date','original_admission_date'=>'date','leave_date'=>'date','is_readmitted'=>'boolean']; }
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
    public function promotionResults() { return $this->hasMany(PromotionResult::class); }
    public function previousClassroom() { return $this->belongsTo(Classroom::class, 'previous_class_id'); }
    public function previousSection() { return $this->belongsTo(Section::class, 'previous_section_id'); }
    public function enrollments() { return $this->hasMany(StudentEnrollment::class); }
    public function currentEnrollment() { return $this->hasOne(StudentEnrollment::class)->where('academic_year_id', AcademicYear::where('is_current', true)->value('id') ?? AcademicYear::max('id'))->where('status', 'enrolled'); }
    public function comments() { return $this->hasMany(StudentComment::class)->latestFirst(); }
    public function reportComments() { return $this->hasMany(StudentComment::class)->forReport()->latestFirst(); }

    /**
     * Full name is now a real column.
     * Accessor kept for backward compatibility but just returns the column value.
     */
    public function getFullNameAttribute()
    {
        return $this->attributes['full_name'] ?? '';
    }

    /**
     * Backward-compatible accessor: split full_name into first part.
     * Used by legacy Blade views that still reference $student->first_name.
     */
    public function getFirstNameAttribute()
    {
        $full = $this->attributes['full_name'] ?? '';
        return explode(' ', $full)[0] ?? '';
    }

    /**
     * Backward-compatible accessor: split full_name into remaining parts.
     * Used by legacy Blade views that still reference $student->last_name.
     */
    public function getLastNameAttribute()
    {
        $full = $this->attributes['full_name'] ?? '';
        $parts = explode(' ', $full);
        array_shift($parts);
        return implode(' ', $parts);
    }

    /**
     * Scope: only active students
     */
    public function scopeActive($query) { return $query->where('status', 'active'); }

    /**
     * Scope: students who left (inactive/transferred/graduated)
     */
    public function scopeLeftSchool($query) { return $query->whereIn('status', ['inactive', 'transferred']); }

    /**
     * Check if student can be readmitted
     */
    public function canBeReadmitted(): bool
    {
        return in_array($this->status, ['inactive', 'transferred']);
    }
}