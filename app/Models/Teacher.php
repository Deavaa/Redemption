<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Teacher extends Model {
protected $fillable = ['user_id','department_id','full_name','email','phone','qualification','department','hire_date','salary','status','address','photo'];

protected $casts = [
    'hire_date' => 'date',
    'salary' => 'decimal:2',
];

public function user() { return $this->belongsTo(User::class); }

/**
 * Full name is now a real column.
 * Accessor kept for backward compatibility but just returns the column value.
 */
public function getFullNameAttribute() { return $this->attributes['full_name'] ?? ''; }

/**
 * Backward-compatible accessor: split full_name into first part.
 */
public function getFirstNameAttribute()
{
    $full = $this->attributes['full_name'] ?? '';
    return explode(' ', $full)[0] ?? '';
}

/**
 * Backward-compatible accessor: split full_name into remaining parts.
 */
public function getLastNameAttribute()
{
    $full = $this->attributes['full_name'] ?? '';
    $parts = explode(' ', $full);
    array_shift($parts);
    return implode(' ', $parts);
}

public function sections() { return $this->hasMany(Section::class, 'teacher_id'); }
public function classRooms() { return $this->hasMany(ClassRoom::class, 'teacher_id'); }
public function assignments() { return $this->hasMany(TeacherAssignment::class, 'teacher_id'); }
public function branchPrincipal() { return $this->hasOne(Branch::class, 'principal_id'); }
public function department() { return $this->belongsTo(Department::class, 'department_id'); }
public function examQuestions() { return $this->hasMany(ExamQuestion::class, 'teacher_id'); }
}
