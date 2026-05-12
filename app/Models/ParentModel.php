<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentModel extends Model
{
    use HasFactory;
    protected $table = 'parents';
    protected $fillable = ['user_id','father_name','mother_name','father_occupation','mother_occupation','father_phone','mother_phone','guardian_name','guardian_relation','guardian_phone'];

    // Accessors for compatibility with views expecting 'name' and 'phone'
    public function getNameAttribute()
    {
        return $this->father_name;
    }

    public function getPhoneAttribute()
    {
        return $this->father_phone;
    }

    public function user() { return $this->belongsTo(User::class); }
    public function students() { return $this->belongsToMany(Student::class, 'student_parent', 'parent_id', 'student_id')->withPivot('relation')->withTimestamps(); }
}