<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class TeacherAssignment extends Model
{
    use HasFactory;
    protected $fillable = ['teacher_id','class_id','section_id','subject_id','academic_year_id'];
    public function subject() { return $this->belongsTo(Subject::class); }
    public function classRoom() { return $this->belongsTo(Classroom::class, 'class_id'); }
    public function section() { return $this->belongsTo(Section::class); }
    public function teacher() { return $this->belongsTo(\App\Models\Teacher::class, 'teacher_id'); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
}