<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['name','address','phone','email','principal_id','gps_lat','gps_lng','map_embed_url','is_active','is_headquarters','order'];
    protected $casts = ['is_active'=>'boolean','is_headquarters'=>'boolean'];

    /**
     * Legacy: single principal (kept for backward compatibility).
     */
    public function principal()
    {
        return $this->belongsTo(Teacher::class,'principal_id');
    }

    /**
     * Multiple principals via pivot table.
     * A branch may have multiple principals.
     */
    public function principals()
    {
        return $this->belongsToMany(Teacher::class, 'branch_principals')
            ->withPivot('is_primary', 'assigned_date')
            ->withTimestamps();
    }

    /**
     * Get the primary principal for this branch.
     */
    public function primaryPrincipal()
    {
        return $this->belongsToMany(Teacher::class, 'branch_principals')
            ->wherePivot('is_primary', true)
            ->withPivot('is_primary', 'assigned_date')
            ->withTimestamps();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function classes()
    {
        return $this->hasMany(Classroom::class);
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function sections()
    {
        // Sections don't have branch_id directly; they belong to classes which have branch_id
        return $this->hasManyThrough(Section::class, Classroom::class, 'branch_id', 'class_id');
    }

    /**
     * Employees assigned to this branch (primary or secondary).
     */
    public function employees()
    {
        return $this->belongsToMany(User::class, 'employee_branch')
            ->withPivot('role_in_branch')
            ->withTimestamps();
    }
}
