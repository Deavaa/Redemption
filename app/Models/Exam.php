<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Exam extends Model
{
    use HasFactory;
    protected $fillable = ['academic_year_id','term_id','name','type','start_date','end_date','start_time','end_time','total_marks','passing_marks','description','class_id','subject_id'];
    protected $casts = ['start_date'=>'date','end_date'=>'date'];
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function term() { return $this->belongsTo(Term::class); }
    public function classRoom() { return $this->belongsTo(ClassRoom::class, 'class_id'); }
    public function subject() { return $this->belongsTo(Subject::class); }
}