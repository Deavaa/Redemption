<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkEntry extends Model
{
    use HasFactory;
    protected $table = 'mark_entries';
    protected $fillable = [
        'exam_id','student_id','subject_id','academic_year_id','term_id','class_id','section_id','class_grade','section','teacher_id',
        'marks_obtained','grade','remarks',
        'ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10',
        'conduct','handwriting','creativity',
        'test1','test2','mid_term','final_exam',
        'ca_total','exam_total','grand_total',
    ];

    /**
     * Get mark fields from config (with hardcoded fallback)
     */
    public static function getMarkFields(): array
    {
        return \App\Models\MarkEntryConfig::getMarkFields();
    }

    /**
     * Calculate totals using DB-driven configuration
     */
    public static function calcTotals(array $data): array
    {
        $markFields = static::getMarkFields();
        $caWeight = MarkEntryConfig::getCaWeight();
        $examWeight = MarkEntryConfig::getExamWeight();
        $precision = MarkEntryConfig::getRoundingPrecision();
        $gradeScale = MarkEntryConfig::getGradeScale();

        // Calculate CA raw total from all CA and Extra CA fields
        $caRawTotal = 0;
        $caFields = [];
        $examFields = [];

        foreach ($markFields as $field) {
            $cat = $field['category'] ?? 'ca';
            $col = $field['col'];
            if ($cat === 'ca' || $cat === 'extra_ca') {
                $caFields[] = $col;
                $caRawTotal += $field['max'];
            } elseif ($cat === 'exam') {
                $examFields[] = $col;
            }
        }

        // Sum raw marks
        $caRaw = 0;
        foreach ($caFields as $f) $caRaw += floatval($data[$f] ?? 0);
        $examRaw = 0;
        foreach ($examFields as $f) $examRaw += floatval($data[$f] ?? 0);

        // Scale CA to its weight percentage
        $caTotal = $caRawTotal > 0
            ? round(($caRaw / $caRawTotal) * $caWeight, $precision)
            : 0;

        // Cap exam at exam weight
        $examTotal = min($examRaw, $examWeight);

        $data['ca_total'] = $caTotal;
        $data['exam_total'] = $examTotal;
        $data['grand_total'] = round($caTotal + $examTotal, $precision);

        // Calculate grade using DB-driven grade scale
        $gt = $data['grand_total'];
        $data['grade'] = static::calcGrade($gt, $gradeScale);

        return $data;
    }

    /**
     * Calculate grade from score using DB-driven grade scale
     */
    public static function calcGrade(float $score, ?array $gradeScale = null): string
    {
        $gradeScale = $gradeScale ?? MarkEntryConfig::getGradeScale();

        // Handle 0 / empty as Incomplete
        if ($score <= 0) return 'I';

        // Sort descending by min score
        usort($gradeScale, fn($a, $b) => $b['min'] <=> $a['min']);

        foreach ($gradeScale as $gs) {
            $min = floatval($gs['min'] ?? 0);
            if ($score >= $min) {
                return $gs['grade'] ?? 'F';
            }
        }

        return 'F';
    }

    public function student() { return $this->belongsTo(Student::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function exam() { return $this->belongsTo(Exam::class); }
    public function term() { return $this->belongsTo(Term::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function classRoom() { return $this->belongsTo(Classroom::class, 'class_id'); }
    public function section() { return $this->belongsTo(Section::class); }
    public function teacher() { return $this->belongsTo(Teacher::class); }
}
