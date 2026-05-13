<?php
namespace App\Http\Controllers\IdCard;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Section;
use App\Models\IdCard;
use Illuminate\Http\Request;

class IdCardGenerateController extends Controller
{
    public function index()
    {
        $classes = Classroom::orderBy('name')->get();
        return view('admin.id-card-generate.index', compact('classes'));
    }

    public function getSections(Request $r)
    {
        return response()->json(
            Section::where('class_id', $r->class_id)->orderBy('name')->get(['id', 'name'])
        );
    }

    public function getStudents(Request $r)
    {
        $query = Student::with('section', 'classroom');
        if ($r->filled('class_id')) $query->where('class_id', $r->class_id);
        if ($r->filled('section_id')) $query->where('section_id', $r->section_id);
        return response()->json(
            $query->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'roll_number', 'class_id', 'section_id'])
        );
    }

    public function generate(Request $r)
    {
        $r->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        $students = Student::with(['classroom', 'section', 'branch'])->whereIn('id', $r->student_ids)->get();

        // Auto-create ID card records for students who don't have one
        foreach ($students as $student) {
            if (!$student->idCards()->exists()) {
                IdCard::create([
                    'student_id' => $student->id,
                    'card_number' => 'ID-' . str_pad($student->id, 5, '0', STR_PAD_LEFT),
                    'issue_date' => now()->format('Y-m-d'),
                    'valid_until' => now()->addYear()->format('Y-m-d'),
                    'status' => 'active',
                ]);
            }
        }

        return view('admin.id-card-generate.print', compact('students'));
    }
}
