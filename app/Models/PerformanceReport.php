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