<?php
namespace App\Http\Controllers\Certificate;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\MarkEntry;
use App\Models\Certificate;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\FeePayment;
use App\Models\ProgressReport;
use Illuminate\Http\Request;

class TranscriptController extends Controller
{
    public function index()
    {
        $classes = Classroom::orderBy('name')->get();
        return view('admin.certificate-generate.transcript-index', compact('classes'));
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
        ]);

        $student = Student::with([
            'classroom', 'section', 'branch',
            'parents',
        ])->findOrFail($r->student_id);

        // Get ALL mark entries across ALL academic years, grouped by year then term
        $allMarks = MarkEntry::with(['subject', 'term', 'academicYear', 'classRoom'])
            ->where('student_id', $student->id)
            ->orderBy('academic_year_id')
            ->orderBy('term_id')
            ->orderBy('subject_id')
            ->get();

        // Group by academic year
        $yearsData = [];
        $academicYears = $allMarks->groupBy('academic_year_id');

        foreach ($academicYears as $yearId => $yearMarks) {
            $ay = $yearMarks->first()->academicYear;
            $termsData = [];

            // Group by term within this year
            $terms = $yearMarks->groupBy('term_id');
            foreach ($terms as $termId => $termMarks) {
                $term = $termMarks->first()->term;
                $subjects = [];
                $totalGrand = 0;
                $subjectCount = 0;

                // Group by subject (in case multiple exams per subject per term)
                $subjectMarks = $termMarks->groupBy('subject_id');
                foreach ($subjectMarks as $subjectId => $marks) {
                    $subject = $marks->first()->subject;
                    // Take the best mark if multiple exams
                    $bestMark = $marks->sortByDesc('grand_total')->first();

                    $subjects[] = [
                        'name' => $subject ? $subject->name : 'Unknown',
                        'code' => $subject ? $subject->code : '-',
                        'ca_total' => $bestMark->ca_total,
                        'exam_total' => $bestMark->exam_total,
                        'grand_total' => $bestMark->grand_total,
                        'grade' => $bestMark->grade,
                        'remarks' => $bestMark->remarks,
                    ];
                    $totalGrand += $bestMark->grand_total;
                    $subjectCount++;
                }

                $average = $subjectCount > 0 ? round($totalGrand / $subjectCount, 2) : 0;

                $termsData[] = [
                    'term' => $term,
                    'term_name' => $term ? $term->name : 'Unknown Term',
                    'subjects' => $subjects,
                    'total' => $totalGrand,
                    'average' => $average,
                    'subject_count' => $subjectCount,
                ];
            }

            // Calculate year average
            $yearTotalAvg = 0;
            $yearSubjectCount = 0;
            foreach ($termsData as $td) {
                $yearTotalAvg += $td['average'];
                $yearSubjectCount++;
            }
            $yearAverage = $yearSubjectCount > 0 ? round($yearTotalAvg / $yearSubjectCount, 2) : 0;

            // Get class info from marks
            $classRoom = $yearMarks->first()->classRoom;

            // Get class rank if available
            $classRank = null;
            $progressReport = ProgressReport::where('student_id', $student->id)
                ->where('academic_year_id', $yearId)
                ->orderByDesc('id')
                ->first();
            if ($progressReport) {
                $classRank = $progressReport->class_rank ?? $progressReport->rank;
            }

            $yearsData[] = [
                'academic_year' => $ay,
                'year_name' => $ay ? $ay->name : 'Unknown Year',
                'class_name' => $classRoom ? $classRoom->name : '-',
                'terms' => $termsData,
                'year_average' => $yearAverage,
                'class_rank' => $classRank,
            ];
        }

        // Get fee payment summary
        $feeSummary = FeePayment::where('student_id', $student->id)
            ->selectRaw('
                COUNT(*) as total_payments,
                SUM(amount_paid) as total_paid,
                SUM(CASE WHEN status = "paid" THEN amount_paid ELSE 0 END) as paid_amount,
                SUM(CASE WHEN status = "pending" OR status = "overdue" THEN amount_due - amount_paid ELSE 0 END) as outstanding
            ')
            ->first();

        // Auto-create certificate record
        $prefix = 'TRA';
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
            'type' => 'transcript',
            'certificate_number' => $certificateNumber,
            'issue_date' => now()->format('Y-m-d'),
            'content' => 'Academic transcript for ' . $student->first_name . ' ' . $student->last_name,
            'template' => 'transcript',
        ]);

        return view('admin.certificate-generate.transcript', compact(
            'student', 'yearsData', 'feeSummary', 'cert'
        ));
    }
}
