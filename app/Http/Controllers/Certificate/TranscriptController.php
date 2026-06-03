<?php
namespace App\Http\Controllers\Certificate;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\ClassRoom;
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
        $classes = ClassRoom::orderBy('numeric_name')->orderBy('name')->get();

        // If student_id is provided, directly generate the transcript
        $preselectedStudentId = request()->query('student_id');
        if ($preselectedStudentId) {
            $student = Student::with(['classroom', 'section', 'branch', 'parents'])->find($preselectedStudentId);
            if ($student) {
                // Simulate a generate request
                $request = new Request(['student_id' => $preselectedStudentId]);
                return $this->generate($request);
            }
        }

        return view('admin.certificate-generate.transcript-index', compact('classes'));
    }

    /**
     * Handle GET /transcript/generate — redirect to the transcript form
     * (replaces the closure route to avoid route-cache issues)
     */
    public function generateForm()
    {
        return redirect()->route('admin.transcript.index');
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
        ]);

        $student = Student::with([
            'classroom', 'section', 'branch',
            'parents',
        ])->findOrFail($r->student_id);

        // Get ALL mark entries across ALL academic years
        $allMarks = MarkEntry::with(['subject', 'term', 'academicYear', 'classRoom'])
            ->where('student_id', $student->id)
            ->orderBy('academic_year_id')
            ->orderBy('term_id')
            ->orderBy('subject_id')
            ->get();

        // ── Build the new data structure ──
        // Goal: one row per subject, columns = academic years, each year has Term1 | Term2 | Annual
        // Also need to track all unique terms (could be 2 or 3 per year)

        $yearColumns = [];   // ordered list of year info
        $subjectRows  = [];  // [subjectName => [yearIndex => [term1 => score, term2 => score, ..., annual => score]]]
        $yearTotals   = [];  // [yearIndex => [term1 => sum, term2 => sum, ..., annual => sum, count => n]]
        $yearAverages = [];
        $yearRanks    = [];

        // 1) Identify all unique terms across all years (e.g. Term 1, Term 2, Term 3)
        $allTermNames = [];
        $allMarks->each(function ($m) use (&$allTermNames) {
            $tName = $m->term ? $m->term->name : 'Term';
            if (!in_array($tName, $allTermNames)) {
                $allTermNames[] = $tName;
            }
        });
        // Sort term names naturally (Term 1, Term 2, Term 3...)
        natsort($allTermNames);
        $allTermNames = array_values($allTermNames);
        $termCount = count($allTermNames);

        // 2) Group marks by academic year
        $academicYears = $allMarks->groupBy('academic_year_id');

        foreach ($academicYears as $yearId => $yearMarks) {
            $ay = $yearMarks->first()->academicYear;
            $classRoom = $yearMarks->first()->classRoom;

            $yearIndex = count($yearColumns);
            $yearColumns[] = [
                'year_name'  => $ay ? $ay->name : 'Unknown Year',
                'class_name' => $classRoom ? $classRoom->name : '-',
            ];

            // Initialize totals for this year
            $yearTotals[$yearIndex] = array_fill_keys($allTermNames, 0);
            $yearTotals[$yearIndex]['annual'] = 0;
            $yearTotals[$yearIndex]['count'] = 0;

            // Group by term within this year
            $terms = $yearMarks->groupBy('term_id');

            // Build a map: termName => [subjectId => bestGrandTotal]
            $termSubjectScores = [];
            foreach ($terms as $termId => $termMarks) {
                $term = $termMarks->first()->term;
                $tName = $term ? $term->name : 'Term';

                $subjectMarks = $termMarks->groupBy('subject_id');
                foreach ($subjectMarks as $subjectId => $marks) {
                    $subject = $marks->first()->subject;
                    $sName = $subject ? $subject->name : 'Unknown';
                    $bestMark = $marks->sortByDesc('grand_total')->first();

                    // Store in subjectRows
                    if (!isset($subjectRows[$sName])) {
                        $subjectRows[$sName] = [];
                    }
                    if (!isset($subjectRows[$sName][$yearIndex])) {
                        $subjectRows[$sName][$yearIndex] = array_fill_keys(array_merge($allTermNames, ['annual']), null);
                    }

                    $score = $bestMark->grand_total ?? 0;
                    $subjectRows[$sName][$yearIndex][$tName] = $score;

                    // Track for totals
                    $yearTotals[$yearIndex][$tName] = ($yearTotals[$yearIndex][$tName] ?? 0) + $score;
                }
            }

            // Calculate annual (average of all terms) for each subject in this year
            foreach ($subjectRows as $sName => &$yearData) {
                if (isset($yearData[$yearIndex])) {
                    $scores = [];
                    foreach ($allTermNames as $tName) {
                        if ($yearData[$yearIndex][$tName] !== null) {
                            $scores[] = $yearData[$yearIndex][$tName];
                        }
                    }
                    $yearData[$yearIndex]['annual'] = count($scores) > 0
                        ? round(array_sum($scores) / count($scores), 1)
                        : null;
                }
            }
            unset($yearData);

            // Calculate year totals for annual column
            $annualSum = 0;
            $subjectCountForYear = 0;
            foreach ($subjectRows as $sName => $yearData) {
                if (isset($yearData[$yearIndex]) && $yearData[$yearIndex]['annual'] !== null) {
                    $annualSum += $yearData[$yearIndex]['annual'];
                    $subjectCountForYear++;
                }
            }
            $yearTotals[$yearIndex]['annual'] = $annualSum;
            $yearTotals[$yearIndex]['count'] = $subjectCountForYear;

            $yearAverages[$yearIndex] = $subjectCountForYear > 0
                ? round($annualSum / $subjectCountForYear, 2)
                : 0;

            // Class rank
            $classRank = null;
            $progressReport = ProgressReport::where('student_id', $student->id)
                ->where('academic_year_id', $yearId)
                ->orderByDesc('id')
                ->first();
            if ($progressReport) {
                $classRank = $progressReport->class_rank ?? $progressReport->rank;
            }
            $yearRanks[$yearIndex] = $classRank;
        }

        // Sort subjects alphabetically
        ksort($subjectRows);

        // Get fee payment summary (join with fees table to get total amount)
        $feeSummary = FeePayment::join('fees', 'fee_payments.fee_id', '=', 'fees.id')
            ->where('fee_payments.student_id', $student->id)
            ->selectRaw('
                COUNT(*) as total_payments,
                SUM(fee_payments.amount_paid) as total_paid,
                SUM(CASE WHEN fee_payments.status = "paid" THEN fee_payments.amount_paid ELSE 0 END) as paid_amount,
                SUM(CASE WHEN fee_payments.status = "pending" OR fee_payments.status = "overdue" OR fee_payments.status = "partial" THEN fees.amount - fee_payments.amount_paid ELSE 0 END) as outstanding
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
            'content' => 'Academic transcript for ' . $student->full_name,
            'template' => 'transcript',
        ]);

        return view('admin.certificate-generate.transcript', compact(
            'student', 'cert', 'feeSummary',
            'yearColumns', 'subjectRows', 'yearTotals', 'yearAverages', 'yearRanks',
            'allTermNames', 'termCount'
        ));
    }
}
