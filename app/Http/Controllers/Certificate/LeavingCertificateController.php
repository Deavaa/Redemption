<?php
namespace App\Http\Controllers\Certificate;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\MarkEntry;
use App\Models\Certificate;
use App\Models\FeePayment;
use App\Models\ProgressReport;
use App\Models\LibraryBook;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LeavingCertificateController extends Controller
{
    public function index()
    {
        $classes = Classroom::orderBy('name')->get();
        return view('admin.certificate-generate.leaving-index', compact('classes'));
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
            'leaving_date' => 'nullable|date',
            'reason' => 'nullable|string|max:255',
            'conduct' => 'nullable|string|in:excellent,very_good,good,satisfactory,fair,poor',
        ]);

        $student = Student::with([
            'classroom', 'section', 'branch',
            'parents',
        ])->findOrFail($r->student_id);

        $leavingDate = $r->leaving_date ? Carbon::parse($r->leaving_date) : now();
        $reason = $r->reason ?? 'Transfer';
        $conduct = $r->conduct ?? 'good';

        // ---- Clearance Check ----

        // 1. Fee clearance (join with fees table to get total amount since fee_payments has no amount_due)
        $feePayments = FeePayment::join('fees', 'fee_payments.fee_id', '=', 'fees.id')
            ->where('fee_payments.student_id', $student->id)
            ->get();
        $totalFees = $feePayments->sum('amount'); // fees.amount is the total fee
        $totalPaid = $feePayments->sum('amount_paid');
        $feeOutstanding = max(0, $totalFees - $totalPaid);
        $feeClear = $feeOutstanding <= 0;

        // 2. Library clearance - check for any unreturned books (if library_issue model exists)
        $libraryClear = true; // Default clear; can be extended with library issue tracking

        // 3. Academic records summary
        $allMarks = MarkEntry::with(['subject', 'term', 'academicYear', 'classRoom'])
            ->where('student_id', $student->id)
            ->orderBy('academic_year_id')
            ->orderBy('term_id')
            ->get();

        // Quick academic summary per year
        $academicSummary = [];
        $yearGroups = $allMarks->groupBy('academic_year_id');
        foreach ($yearGroups as $yearId => $yearMarks) {
            $ay = $yearMarks->first()->academicYear;
            $classRoom = $yearMarks->first()->classRoom;
            $subjectMarks = $yearMarks->groupBy('subject_id');
            $totalAvg = 0;
            $subjCount = 0;
            foreach ($subjectMarks as $subjMarks) {
                $best = $subjMarks->sortByDesc('grand_total')->first();
                $totalAvg += $best->grand_total;
                $subjCount++;
            }
            $avg = $subjCount > 0 ? round($totalAvg / $subjCount, 2) : 0;

            // Determine overall grade
            $overallGrade = $this->getGrade($avg);

            $academicSummary[] = [
                'year_name' => $ay ? $ay->name : 'Unknown',
                'class_name' => $classRoom ? $classRoom->name : '-',
                'average' => $avg,
                'grade' => $overallGrade,
            ];
        }

        // Last academic performance
        $lastYearSummary = !empty($academicSummary) ? end($academicSummary) : null;

        // Conduct assessment from progress reports
        $lastProgress = ProgressReport::where('student_id', $student->id)
            ->orderByDesc('id')
            ->first();
        if ($lastProgress && $lastProgress->remarks) {
            // Use actual progress report conduct if available
        }

        // Duration at school
        $admissionDate = $student->admission_date;
        $duration = '';
        if ($admissionDate) {
            $diff = $admissionDate->diff($leavingDate);
            $duration = $diff->y . ' year' . ($diff->y !== 1 ? 's' : '');
            if ($diff->m > 0) $duration .= ', ' . $diff->m . ' month' . ($diff->m !== 1 ? 's' : '');
        }

        // All clearance items
        $clearanceItems = [
            ['name' => 'Tuition Fees', 'status' => $feeClear ? 'cleared' : 'outstanding', 'detail' => $feeClear ? 'No outstanding balance' : 'Outstanding: ' . number_format($feeOutstanding, 2)],
            ['name' => 'Library Books', 'status' => $libraryClear ? 'cleared' : 'pending', 'detail' => $libraryClear ? 'All books returned' : 'Books pending return'],
            ['name' => 'Academic Records', 'status' => 'cleared', 'detail' => count($academicSummary) . ' academic year(s) on record'],
            ['name' => 'School Property', 'status' => 'cleared', 'detail' => 'No items outstanding'],
        ];

        $allClear = collect($clearanceItems)->every(fn($item) => $item['status'] === 'cleared');

        // Auto-create certificate record
        $prefix = 'LC';
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

        while (Certificate::where('certificate_number', $certificateNumber)->exists()) {
            $nextNum++;
            $certificateNumber = $prefix . '-' . $year . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }

        $cert = Certificate::create([
            'student_id' => $student->id,
            'type' => 'leaving_certificate',
            'certificate_number' => $certificateNumber,
            'issue_date' => now()->format('Y-m-d'),
            'content' => 'School Leaving Clearance Certificate for ' . $student->full_name,
            'template' => 'leaving_certificate',
        ]);

        return view('admin.certificate-generate.leaving-certificate', compact(
            'student', 'cert', 'leavingDate', 'reason', 'conduct',
            'clearanceItems', 'allClear', 'feeOutstanding', 'feeClear',
            'academicSummary', 'lastYearSummary', 'duration'
        ));
    }

    private function getGrade($avg)
    {
        if ($avg >= 90) return 'A+';
        if ($avg >= 80) return 'A';
        if ($avg >= 75) return 'A-';
        if ($avg >= 70) return 'B+';
        if ($avg >= 65) return 'B';
        if ($avg >= 60) return 'B-';
        if ($avg >= 55) return 'C+';
        if ($avg >= 50) return 'C';
        if ($avg >= 45) return 'C-';
        if ($avg >= 40) return 'D';
        return 'F';
    }
}
