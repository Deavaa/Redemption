<?php
file_put_contents("app/Models/MarkEntry.php", '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkEntry extends Model
{
    use HasFactory;

    protected $table = "mark_entries";

    protected $fillable = [
        "student_id", "exam_id", "subject_id",
        "marks_obtained", "max_marks", "remarks"
    ];

    protected $casts = [
        "marks_obtained" => "decimal:2",
        "max_marks" => "decimal:2",
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
');
echo "MarkEntry model fixed.";
