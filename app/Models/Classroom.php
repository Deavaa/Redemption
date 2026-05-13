<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ClassRoom extends Model
{
    use HasFactory;
    protected $table = 'classes';
    protected $fillable = ['branch_id','academic_year_id','name','numeric_name','teacher_id','capacity'];
    public function sections() { return $this->hasMany(Section::class, 'class_id'); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
    public function teacher() { return $this->belongsTo(Teacher::class, 'teacher_id'); }
}