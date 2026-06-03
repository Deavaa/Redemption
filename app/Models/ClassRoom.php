<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ClassRoom extends Model
{
    use HasFactory;
    protected $table = 'classes';
    protected $fillable = ['branch_id','academic_year_id','name','numeric_name','capacity'];

    /**
     * Calculated capacity = sum of all sections' max_students.
     * Falls back to the stored capacity column if no sections exist.
     */
    public function getCalculatedCapacityAttribute()
    {
        if ($this->relationLoaded('sections') || $this->sections()->exists()) {
            $sum = $this->sections->sum('max_students');
            if ($sum > 0) return $sum;
        }
        return $this->capacity;
    }

    /**
     * Recalculate and save capacity from sections.
     */
    public function recalculateCapacity(): void
    {
        $this->capacity = $this->sections()->sum('max_students') ?: null;
        $this->saveQuietly();
    }

    public function sections() { return $this->hasMany(Section::class, 'class_id')->orderBy('name'); }
    public function students() { return $this->hasMany(Student::class, 'class_id'); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function branch() { return $this->belongsTo(Branch::class); }
}