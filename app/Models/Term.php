<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Term extends Model
{
    use HasFactory;
    protected $fillable = ['academic_year_id','name','start_date','end_date','exam_start_date','exam_end_date','term_number','is_active'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','exam_start_date'=>'date','exam_end_date'=>'date','is_active'=>'boolean'];
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }

    /**
     * Get the "current" term — the active term within the current academic year.
     * Falls back to the most recently created term if no active term is found.
     * Returns null if there are no terms at all.
     */
    public static function getCurrent(): ?self
    {
        $ay = AcademicYear::getActive();
        if (!$ay) {
            return static::latest('id')->first();
        }
        // Try: active term in the current academic year
        $term = static::where('academic_year_id', $ay->id)
            ->where('is_active', true)
            ->orderBy('term_number')
            ->first();
        if ($term) return $term;
        // Fallback: latest term in the current academic year
        return static::where('academic_year_id', $ay->id)
            ->orderByDesc('term_number')
            ->first();
    }

    /**
     * Get the ID of the current term (0 if no term exists).
     * Convenient for controller defaults.
     */
    public static function getCurrentId(): int
    {
        $term = static::getCurrent();
        return $term ? $term->id : 0;
    }
}