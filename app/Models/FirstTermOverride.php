<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FirstTermOverride extends Model
{
    protected $table = 'first_term_overrides';
    protected $fillable = [
        'student_id', 'subject_id', 'academic_year_id',
        'class_id', 'section_id',
        'grand_total', 'grade', 'rank_override', 'notes',
    ];

    protected $casts = [
        'grand_total' => 'float',
        'rank_override' => 'integer',
    ];

    public function student() { return $this->belongsTo(Student::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function classRoom() { return $this->belongsTo(ClassRoom::class, 'class_id'); }
    public function section() { return $this->belongsTo(Section::class); }
}
