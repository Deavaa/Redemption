<?php
namespace App\Http\Controllers\Certificate;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Certificate;
use App\Models\MarkEntry;
use Illuminate\Http\Request;

class CertificateGenerateController extends Controller
{
    public function index()
    {
        $classes = Classroom::orderBy('name')->get();
        return view('admin.certificate-generate.index', compact('classes'));
    }

    public function getStudents(Request $r)
    {
        $query = Student::with('classroom', 'section');
        if ($r->filled('class_id')) $query->where('class_id', $r->class_id);
        if ($r->filled('section_id')) $query->where('section_id', $r->section_id);
        return response()->json($query->orderBy('first_name')->get());
    }

    public function generate(Request $r)
    {
        $r->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:academic,completion,transfer,character,foldable',
        ]);

        $student = Student::with(['classroom', 'section', 'branch'])->findOrFail($r->student_id);
        $marks = MarkEntry::with('subject')
            ->where('student_id', $student->id)
            ->orderBy('subject_id')
            ->get();

        Certificate::where('student_id', $student->id)->where('type', $r->type)->delete();

        // Auto-create certificate record
        $cert = Certificate::create([
            'student_id' => $student->id,
            'type' => $r->type,
            'certificate_number' => strtoupper(substr($r->type, 0, 3)) . '-' . date('Y') . '-' . str_pad(Certificate::count()+1, 4, '0', STR_PAD_LEFT),
            'issue_date' => now()->format('Y-m-d'),
            'content' => $r->type . ' certificate for ' . $student->first_name . ' ' . $student->last_name,
            'template' => $r->type,
        ]);

        if ($r->type === 'foldable') {
            return view('admin.certificate-generate.foldable', compact('student', 'marks', 'cert'));
        }

        return view('admin.certificate-generate.certificate', compact('student', 'marks', 'cert'));
    }
}
