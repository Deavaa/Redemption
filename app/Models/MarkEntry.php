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
    public static function getMarkFields() {
        return [
            ['col'=>'ca1','max'=>5],['col'=>'ca2','max'=>5],['col'=>'ca3','max'=>5],['col'=>'ca4','max'=>5],['col'=>'ca5','max'=>5],
            ['col'=>'ca6','max'=>5],['col'=>'ca7','max'=>5],['col'=>'ca8','max'=>5],['col'=>'ca9','max'=>5],['col'=>'ca10','max'=>5],
            ['col'=>'conduct','max'=>5],['col'=>'handwriting','max'=>5],['col'=>'creativity','max'=>10],
            ['col'=>'test1','max'=>10],['col'=>'test2','max'=>10],['col'=>'mid_term','max'=>20],['col'=>'final_exam','max'=>30],
        ];
    }
    public static function calcTotals(array $data): array {
        $caFields = ['ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10','conduct','handwriting','creativity'];
        $examFields = ['test1','test2','mid_term','final_exam'];
        $caRaw = 0;
        foreach ($caFields as $f) $caRaw += floatval($data[$f] ?? 0);
        $examRaw = 0;
        foreach ($examFields as $f) $examRaw += floatval($data[$f] ?? 0);
        $caTotal = round(($caRaw / 70) * 30, 2);
        $examTotal = min($examRaw, 70);
        $data['ca_total'] = $caTotal;
        $data['exam_total'] = $examTotal;
        $data['grand_total'] = round($caTotal + $examTotal, 2);
        return $data;
    }
    public function student() { return $this->belongsTo(Student::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function term() { return $this->belongsTo(Term::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function classRoom() { return $this->belongsTo(ClassRoom::class, 'class_id'); }
    public function section() { return $this->belongsTo(Section::class); }
}