<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;
    protected $fillable = ['name','email','password','role','phone','address','profile_photo','is_active'];
    protected $hidden = ['password','remember_token'];
    protected function casts(): array { return ['email_verified_at'=>'datetime','password'=>'hashed','is_active'=>'boolean']; }
    public function student() { return $this->hasOne(Student::class); }
    public function parent() { return $this->hasOne(ParentModel::class); }
    public function employeeAssets() { return $this->hasMany(EmployeeAsset::class, 'employee_id'); }
    public function leaves() { return $this->hasMany(Leave::class, 'employee_id'); }
    public function payrolls() { return $this->hasMany(Payroll::class, 'employee_id'); }
    public function approvedLeaves() { return $this->hasMany(Leave::class, 'approved_by'); }
    public function teacherProfile() { return $this->hasOne(Teacher::class, 'email', 'email'); }
    public function isAdmin() { return $this->role === 'admin'; }
    public function isTeacher() { return $this->role === 'teacher'; }
}