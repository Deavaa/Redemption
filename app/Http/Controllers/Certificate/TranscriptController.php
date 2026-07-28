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
        $branchScope = request()->attributes->get('branch_scope');

        // Load all classes (grades) ordered by numeric_name (grade level)
        $classesQuery = ClassRoom::with(['branch', 'sections'])
            ->withCount(['students' => function ($q) {
                $q->whereIn('status', ['active', 'graduated']);
            }]);
        if ($branchScope) $classesQuery->where('branch_id', $branchScope);
        $classes = $classesQuery
            ->orderByRaw('CAST(numeric_name AS UNSIGNED) ASC')
            ->orderBy('name')
            ->get();

        // Group classes by grade level (numeric_name) for the multi-select grid
        $classesByGrade = $classes->groupBy(function ($c) {
            return $c->numeric_name ?: 'Other';
        });

        // If student_id is provided, directly generate the transcript
        $preselectedStudentId = request()->query('student_id');
        if ($preselectedStudentId) {
            $student = Student::with(['classroom', 'section', 'branch', 'parents'])->find($preselectedStudentId);
            if ($student) {
                $request = new Request(['student_id' => $preselectedStudentId]);
                return $this->generate($request);
            }
        }

        return view('admin.certificate-generate.transcript-index', compact('classes', 'classesByGrade'));
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

        // Single class_id (legacy) OR multiple class_ids (bulk generate page)
        if ($r->filled('class_id')) {
            $query->where('class_id', $r->class_id);
        } elseif ($r->filled('class_ids')) {
            $classIds = explode(',', $r->class_ids);
            $query->whereIn('class_id', $classIds);
        }
        if ($r->filled('section_id')) $query->where('section_id', $r->section_id);

        // Only include active + graduated students (skip inactive/transferred)
        $query->whereIn('status', ['active', 'graduated']);

        $students = $query->orderBy('full_name')->get();

        // If requested, add a flag indicating whether each student has any
        // grades 9-12 marks (used to disable students without transcript data)
        if ($r->filled('include_marks_check')) {
            $studentIds = $students->pluck('id')->all();
            $studentsWithMarks = MarkEntry::whereIn('student_id', $studentIds)
                ->whereHas('classRoom', function ($q) {
                    $q->whereRaw('CAST(numeric_name AS UNSIGNED) >= 9')
                      ->whereRaw('CAST(numeric_name AS UNSIGNED) <= 12');
                })
                ->pluck('student_id')
                ->unique()
                ->all();
            $studentsWithMarksSet = array_flip($studentsWithMarks);
            $students = $students->map(function ($s) use ($studentsWithMarksSet) {
                $s->has_grades_9_12_marks = isset($studentsWithMarksSet[$s->id]);
                return $s;
            });
        }

        return response()->json($students);
    }

    /**
     * Build the transcript data structure for a single student.
     * Shared between generate() (single) and show() (re-render existing cert).
     *
     * @return array{yearColumns, subjectRows, yearTotals, yearAverages, yearRanks, allTermNames, termCount, feeSummary}
     */
    private function buildTranscriptData(Student $student): array
    {
        // Get ALL mark entries across ALL academic years
        // Filter to grades 9-12 only (for graduating students' transcripts)
        $allMarks = MarkEntry::with(['subject', 'term', 'academicYear', 'classRoom'])
            ->where('student_id', $student->id)
            ->whereHas('classRoom', function ($q) {
                $q->whereRaw('CAST(numeric_name AS UNSIGNED) >= 9')
                  ->whereRaw('CAST(numeric_name AS UNSIGNED) <= 12');
            })
            ->orderBy('academic_year_id')
            ->orderBy('term_id')
            ->orderBy('subject_id')
            ->get();

        $yearColumns = [];
        $subjectRows  = [];
        $yearTotals   = [];
        $yearAverages = [];
        $yearRanks    = [];

        // 1) Identify all unique terms across all years
        $allTermNames = [];
        $allMarks->each(function ($m) use (&$allTermNames) {
            $tName = $m->term ? $m->term->name : 'Term';
            if (!in_array($tName, $allTermNames)) {
                $allTermNames[] = $tName;
            }
        });
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

            $yearTotals[$yearIndex] = array_fill_keys($allTermNames, 0);
            $yearTotals[$yearIndex]['annual'] = 0;
            $yearTotals[$yearIndex]['count'] = 0;

            $terms = $yearMarks->groupBy('term_id');

            foreach ($terms as $termId => $termMarks) {
                $term = $termMarks->first()->term;
                $tName = $term ? $term->name : 'Term';

                $subjectMarks = $termMarks->groupBy('subject_id');
                foreach ($subjectMarks as $subjectId => $marks) {
                    $subject = $marks->first()->subject;
                    $sName = $subject ? $subject->name : 'Unknown';
                    $bestMark = $marks->sortByDesc('grand_total')->first();

                    if (!isset($subjectRows[$sName])) {
                        $subjectRows[$sName] = [];
                    }
                    if (!isset($subjectRows[$sName][$yearIndex])) {
                        $subjectRows[$sName][$yearIndex] = array_fill_keys(array_merge($allTermNames, ['annual']), null);
                    }

                    $score = $bestMark->grand_total ?? 0;
                    $subjectRows[$sName][$yearIndex][$tName] = $score;

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

            // Year totals for annual column
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

        ksort($subjectRows);

        $feeSummary = FeePayment::join('fees', 'fee_payments.fee_id', '=', 'fees.id')
            ->where('fee_payments.student_id', $student->id)
            ->selectRaw('
                COUNT(*) as total_payments,
                SUM(fee_payments.amount_paid) as total_paid,
                SUM(CASE WHEN fee_payments.status = "paid" THEN fee_payments.amount_paid ELSE 0 END) as paid_amount,
                SUM(CASE WHEN fee_payments.status = "pending" OR fee_payments.status = "overdue" OR fee_payments.status = "partial" THEN fees.amount - fee_payments.amount_paid ELSE 0 END) as outstanding
            ')
            ->first();

        return [
            'yearColumns'   => $yearColumns,
            'subjectRows'   => $subjectRows,
            'yearTotals'    => $yearTotals,
            'yearAverages'  => $yearAverages,
            'yearRanks'     => $yearRanks,
            'allTermNames'  => $allTermNames,
            'termCount'     => $termCount,
            'feeSummary'    => $feeSummary,
        ];
    }

    /**
     * Generate the next transcript certificate number (TRA-YYYY-NNNN).
     */
    private function nextCertificateNumber(): string
    {
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
        return $certificateNumber;
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

        $data = $this->buildTranscriptData($student);

        // Auto-create certificate record
        $cert = Certificate::create([
            'student_id' => $student->id,
            'type' => 'transcript',
            'certificate_number' => $this->nextCertificateNumber(),
            'issue_date' => now()->format('Y-m-d'),
            'content' => 'Academic transcript for ' . $student->full_name,
            'template' => 'transcript',
        ]);

        return view('admin.certificate-generate.transcript', compact(
            'student', 'cert', 'feeSummary',
            'yearColumns', 'subjectRows', 'yearTotals', 'yearAverages', 'yearRanks',
            'allTermNames', 'termCount'
        ), $data);
    }

    /**
     * Show an existing transcript by certificate ID — does NOT create a new
     * certificate record. Used for re-viewing already-generated transcripts.
     */
    public function show(Certificate $certificate)
    {
        if ($certificate->type !== 'transcript') {
            abort(404, 'Certificate is not a transcript.');
        }

        $student = Student::with([
            'classroom', 'section', 'branch', 'parents',
        ])->findOrFail($certificate->student_id);

        $data = $this->buildTranscriptData($student);
        $cert = $certificate;

        return view('admin.certificate-generate.transcript', compact(
            'student', 'cert', 'feeSummary',
            'yearColumns', 'subjectRows', 'yearTotals', 'yearAverages', 'yearRanks',
            'allTermNames', 'termCount'
        ), $data);
    }

    /**
     * Bulk generate transcripts for multiple students at once.
     * Creates certificate records for each, then shows a results page
     * with view links.
     */
    public function bulkGenerate(Request $r)
    {
        $r->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        $studentIds = array_unique($r->student_ids);
        $students = Student::with(['classroom', 'section', 'branch'])
            ->whereIn('id', $studentIds)
            ->orderBy('full_name')
            ->get();

        $generated = [];
        $skipped = [];
        $errors = [];

        foreach ($students as $student) {
            try {
                // Check for existing transcript cert — if one already exists for
                // this student today, reuse it instead of creating a duplicate.
                $existing = Certificate::where('student_id', $student->id)
                    ->where('type', 'transcript')
                    ->whereDate('issue_date', now()->toDateString())
                    ->first();

                if ($existing) {
                    // Quick sanity check: does the student have any grade 9-12 marks?
                    $hasMarks = MarkEntry::where('student_id', $student->id)
                        ->whereHas('classRoom', function ($q) {
                            $q->whereRaw('CAST(numeric_name AS UNSIGNED) >= 9')
                              ->whereRaw('CAST(numeric_name AS UNSIGNED) <= 12');
                        })->exists();

                    if (!$hasMarks) {
                        $skipped[] = ['student' => $student, 'reason' => 'No grades 9-12 marks found'];
                        continue;
                    }

                    $generated[] = [
                        'student' => $student,
                        'certificate' => $existing,
                        'reused' => true,
                    ];
                    continue;
                }

                $hasMarks = MarkEntry::where('student_id', $student->id)
                    ->whereHas('classRoom', function ($q) {
                        $q->whereRaw('CAST(numeric_name AS UNSIGNED) >= 9')
                          ->whereRaw('CAST(numeric_name AS UNSIGNED) <= 12');
                    })->exists();

                if (!$hasMarks) {
                    $skipped[] = ['student' => $student, 'reason' => 'No grades 9-12 marks found'];
                    continue;
                }

                $cert = Certificate::create([
                    'student_id' => $student->id,
                    'type' => 'transcript',
                    'certificate_number' => $this->nextCertificateNumber(),
                    'issue_date' => now()->format('Y-m-d'),
                    'content' => 'Academic transcript for ' . $student->full_name,
                    'template' => 'transcript',
                ]);

                $generated[] = [
                    'student' => $student,
                    'certificate' => $cert,
                    'reused' => false,
                ];
            } catch (\Exception $e) {
                $errors[] = ['student' => $student, 'error' => $e->getMessage()];
            }
        }

        return view('admin.certificate-generate.transcript-bulk-results', compact(
            'generated', 'skipped', 'errors'
        ));
    }
}
