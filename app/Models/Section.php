<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Section extends Model
{
    use HasFactory;
    protected $fillable = ['class_id','name','max_students','teacher_id','capacity'];

    public function classRoom() { return $this->belongsTo(ClassRoom::class, 'class_id'); }
    public function teacher() { return $this->belongsTo(Teacher::class, 'teacher_id'); }
}
