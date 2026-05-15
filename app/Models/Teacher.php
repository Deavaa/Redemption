<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Teacher extends Model {
protected $fillable = ['user_id','first_name','last_name','email','phone','qualification','department','hire_date','salary','status','address','photo'];

public function user() { return $this->belongsTo(User::class); }

public function getFullNameAttribute() { return trim($this->first_name . ' ' . $this->last_name); }

public function sections() { return $this->hasMany(Section::class, 'teacher_id'); }
public function classRooms() { return $this->hasMany(ClassRoom::class, 'teacher_id'); }
public function assignments() { return $this->hasMany(TeacherAssignment::class, 'teacher_id'); }
public function branchPrincipal() { return $this->hasOne(Branch::class, 'principal_id'); }
}
