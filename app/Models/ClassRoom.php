<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * ClassRoom Model (primary — self-contained, no external dependencies)
 *
 * This is the CANONICAL model file for the "classes" table.
 * It contains the complete class definition and does NOT depend on
 * Classroom.php. This is critical for shared hosting (ByetHost/cPanel)
 * where the autoloader classmap may be stale and Classroom.php may
 * not exist on the server.
 *
 * At the bottom of this file, class_alias() registers "Classroom" as
 * an alias for "ClassRoom", so both class names work everywhere:
 *   use App\Models\ClassRoom;   ← works (this file)
 *   use App\Models\Classroom;   ← works (via class_alias)
 */
class ClassRoom extends Model
{
    use HasFactory;

    protected $table = 'classes';
    protected $fillable = ['branch_id', 'academic_year_id', 'name', 'numeric_name', 'capacity'];

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

// ── Backward-compatible alias: "Classroom" also works ──
// If Classroom.php exists on the server AND has already been loaded,
// class_alias will be a no-op (PHP ignores aliasing an existing class
// to itself). If Classroom.php doesn't exist (common on shared hosting),
// this ensures `App\Models\Classroom` still resolves.
if (!class_exists('App\Models\Classroom')) {
    class_alias('App\Models\ClassRoom', 'App\Models\Classroom');
}
