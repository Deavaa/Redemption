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
        $classes = Classroom::orderBy('numeric_name')->orderBy('name')->get();
        $preselectedStudentId = request()->query('student_id');
        $preselectedStudent = null;

        if ($preselectedStudentId) {
            $preselectedStudent = Student::with(['classroom', 'section'])->find($preselectedStudentId);

            // If a student is selected from the student list, directly generate the certificate
            if ($preselectedStudent) {
                $type = request()->query('type', 'academic');
                return $this->generateForStudent($preselectedStudentId, $type);
            }
        }

        return view('admin.certificate-generate.index', compact('classes', 'preselectedStudent'));
    }

    public function getStudents(Request $r)
    {
        $query = Student::with('classroom', 'section');
        if ($r->filled('class_id')) $query->where('class_id', $r->class_id);
        if ($r->filled('section_id')) $query->where('section_id', $r->section_id);
        return response()->json($query->orderBy('full_name')->get());
    }

    public function generate(Request $r)
    {
        $r->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:academic,completion,transfer,character,foldable,transcript,leaving_certificate',
        ]);

        return $this->generateForStudent($r->student_id, $r->type);
    }

    /**
     * Shared generation logic used by both the form submit and the direct student link.
     */
    private function generateForStudent($studentId, $type)
    {
        $student = Student::with(['classroom', 'section', 'branch'])->findOrFail($studentId);
        $marks = MarkEntry::with('subject')
            ->where('student_id', $student->id)
            ->orderBy('subject_id')
            ->get();

        // Delete existing certificate of same type for this student (regenerate)
        Certificate::where('student_id', $student->id)->where('type', $type)->delete();

        // Generate unique certificate number using max+1 to avoid duplicate constraint
        $prefix = strtoupper(substr($type, 0, 3));
        $year = date('Y');
        $lastCert = Certificate::where('certificate_number', 'LIKE', "{$prefix}-{$year}-%")
            ->orderByDesc('id')
            ->first();
        $nextNum = 1;
        if ($lastCert) {
            $parts = explode('-', $lastCert->certificate_number);
            $lastNum = (int) end($parts);
            $nextNum = $lastNum + 1;
        }
        $certificateNumber = $prefix . '-' . $year . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

        // Ensure uniqueness (edge case: concurrent requests)
        while (Certificate::where('certificate_number', $certificateNumber)->exists()) {
            $nextNum++;
            $certificateNumber = $prefix . '-' . $year . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }

        // Auto-create certificate record
        $cert = Certificate::create([
            'student_id' => $student->id,
            'type' => $type,
            'certificate_number' => $certificateNumber,
            'issue_date' => now()->format('Y-m-d'),
            'content' => $type . ' certificate for ' . $student->full_name,
            'template' => $type,
        ]);

        // Delegate to dedicated pages for transcript and leaving certificate
        // (these have their own certificate generation logic)
        if ($type === 'transcript') {
            // Delete the certificate we just created — TranscriptController creates its own
            $cert->delete();
            return redirect()->route('admin.transcript.index', ['student_id' => $studentId]);
        }
        if ($type === 'leaving_certificate') {
            return redirect()->route('admin.leaving-certificate.index', ['student_id' => $studentId]);
        }

        if ($type === 'foldable') {
            return view('admin.certificate-generate.foldable', compact('student', 'marks', 'cert'));
        }

        return view('admin.certificate-generate.certificate', compact('student', 'marks', 'cert'));
    }
}
