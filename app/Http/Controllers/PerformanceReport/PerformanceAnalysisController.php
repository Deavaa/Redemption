<?php

namespace App\Http\Controllers\PerformanceReport;

use App\Http\Controllers\Controller;
use App\Models\MarkEntry;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Student;
use Illuminate\Http\Request;

class PerformanceAnalysisController extends Controller
{
    /**
     * Show the filter form for performance analysis.
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $terms = Term::orderBy('id', 'desc')->get();
        $classes = ClassRoom::orderBy('numeric_name')->orderBy('name')->get();

        return view('admin.performance-analysis.index', compact('academicYears', 'terms', 'classes'));
    }

    /**
     * Generate performance analysis from MarkEntry data.
     * Auto-calculates averages, rankings, grade distribution, subject performance.
     */
    public function generate(Request $r)
    {
        // If GET request with no filters, redirect to index
        if ($r->isMethod('GET') && !$r->filled('academic_year_id')) {
            return redirect()->route('admin.performance-analysis.index');
        }

        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $query = MarkEntry::with(['student', 'subject'])
            ->where('academic_year_id', $r->academic_year_id)
            ->where('term_id', $r->term_id)
            ->where('class_id', $r->class_id);

        if ($r->filled('section_id')) {
            $query->where('section_id', $r->section_id);
        }

        $marks = $query->get();

        // Group by student
        $grouped = $marks->groupBy('student_id');

        $analysis = [];
        $allSubjectTotals = []; // subject_id => [grand_totals]

        foreach ($grouped as $studentId => $entries) {
            $student = $entries->first()->student;
            $subjectCount = 0;
            $totalGrand = 0;
            $subjectDetails = [];

            foreach ($entries as $entry) {
                if ($entry->subject) {
                    $subjectCount++;
                    $totalGrand += floatval($entry->grand_total ?? 0);
                    $subjectDetails[] = [
                        'subject' => $entry->subject,
                        'ca_total' => floatval($entry->ca_total ?? 0),
                        'exam_total' => floatval($entry->exam_total ?? 0),
                        'grand_total' => floatval($entry->grand_total ?? 0),
                    ];

                    if (!isset($allSubjectTotals[$entry->subject_id])) {
                        $allSubjectTotals[$entry->subject_id] = [];
                    }
                    $allSubjectTotals[$entry->subject_id][] = floatval($entry->grand_total ?? 0);
                }
            }

            $average = $subjectCount > 0 ? round($totalGrand / $subjectCount, 2) : 0;

            $analysis[] = [
                'student' => $student,
                'total_marks' => round($totalGrand, 2),
                'subject_count' => $subjectCount,
                'average' => $average,
                'subjects' => $subjectDetails,
            ];
        }

        // Sort by average descending for ranking
        usort($analysis, function ($a, $b) {
            return $b['average'] <=> $a['average'];
        });

        // Assign ranks
        $rank = 1;
        foreach ($analysis as &$row) {
            $row['rank'] = $rank++;
        }

        // Subject averages
        $subjectAverages = [];
        foreach ($allSubjectTotals as $subjectId => $totals) {
            $subject = $marks->first(fn($m) => $m->subject_id == $subjectId)?->subject;
            if ($subject) {
                $subjectAverages[] = [
                    'subject' => $subject,
                    'average' => round(array_sum($totals) / count($totals), 2),
                    'highest' => round(max($totals), 2),
                    'lowest' => round(min($totals), 2),
                    'count' => count($totals),
                ];
            }
        }

        // Grade distribution
        $gradeDistribution = ['A+' => 0, 'A' => 0, 'A-' => 0, 'B+' => 0, 'B' => 0, 'B-' => 0, 'C+' => 0, 'C' => 0, 'C-' => 0, 'D' => 0, 'F' => 0];
        foreach ($analysis as $row) {
            $g = $this->getGrade($row['average']);
            $gradeDistribution[$g] = ($gradeDistribution[$g] ?? 0) + 1;
        }

        // Class statistics
        $averages = array_column($analysis, 'average');
        $classStats = [
            'total_students' => count($analysis),
            'class_average' => count($averages) > 0 ? round(array_sum($averages) / count($averages), 2) : 0,
            'highest_average' => count($averages) > 0 ? round(max($averages), 2) : 0,
            'lowest_average' => count($averages) > 0 ? round(min($averages), 2) : 0,
        ];

        $class = ClassRoom::find($r->class_id);
        $section = $r->filled('section_id') ? Section::find($r->section_id) : null;
        $academicYear = AcademicYear::find($r->academic_year_id);
        $term = Term::find($r->term_id);

        return view('admin.performance-analysis.index', compact(
            'analysis', 'subjectAverages', 'gradeDistribution', 'classStats',
            'class', 'section', 'academicYear', 'term',
            'academicYears', 'terms', 'classes'
        ));
    }

    public function getSections(Request $r)
    {
        return response()->json(
            Section::where('class_id', $r->class_id)->orderBy('name')->get(['id', 'name'])
        );
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
