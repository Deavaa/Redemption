<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Section extends Model
{
    use HasFactory;
    protected $fillable = ['class_id','name','max_students','teacher_id'];

    // Relationship method named classRoom() — because PHP method names are
    // case-insensitive, $section->classroom and $section->classRoom both resolve
    // to this single method.  Do NOT add a separate classroom() alias or PHP
    // will throw "Cannot redeclare" fatal error.
    public function classRoom() { return $this->belongsTo(ClassRoom::class, 'class_id'); }
    public function teacher() { return $this->belongsTo(Teacher::class, 'teacher_id'); }

    /**
     * Get the branch through the classroom relationship.
     */
    public function branch()
    {
        return $this->hasOneThrough(Branch::class, ClassRoom::class, 'id', 'id', 'class_id', 'branch_id');
    }
}
