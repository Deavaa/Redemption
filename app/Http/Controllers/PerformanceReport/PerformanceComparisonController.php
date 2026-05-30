<?php
namespace App\Http\Controllers\PerformanceReport;
use App\Http\Controllers\Controller;
use App\Models\MarkEntry;
use App\Models\Branch;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;

class PerformanceComparisonController extends Controller
{
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $terms = Term::orderBy('id', 'desc')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $selectedYear = $request->filled('academic_year_id')
            ? AcademicYear::find($request->academic_year_id)
            : AcademicYear::where('is_current', true)->first();

        $selectedTerm = $request->filled('term_id') ? Term::find($request->term_id) : $terms->first();

        $branchComparison = [];
        $overallStats = ['total_students' => 0, 'total_branches' => 0, 'overall_avg' => 0];

        if ($selectedYear && $selectedTerm) {
            $allAverages = [];

            foreach ($branches as $branch) {
                $studentIds = Student::where('branch_id', $branch->id)->pluck('id');
                $marks = MarkEntry::whereIn('student_id', $studentIds)
                    ->where('academic_year_id', $selectedYear->id)
                    ->where('term_id', $selectedTerm->id)
                    ->get();

                $studentAverages = $marks->groupBy('student_id')->map(function ($entries) {
                    $count = $entries->count();
                    return $count > 0 ? round($entries->avg('grand_total'), 2) : 0;
                });

                $avg = $studentAverages->count() > 0 ? round($studentAverages->avg(), 2) : 0;
                $highest = $studentAverages->count() > 0 ? round($studentAverages->max(), 2) : 0;
                $lowest = $studentAverages->count() > 0 ? round($studentAverages->min(), 2) : 0;

                // Grade distribution
                $gradeDist = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0, 'I' => 0];
                foreach ($studentAverages as $sa) {
                    $g = $this->getGrade($sa);
                    $gradeDist[$g] = ($gradeDist[$g] ?? 0) + 1;
                }

                $branchComparison[] = [
                    'branch' => $branch,
                    'student_count' => $studentIds->count(),
                    'avg_performance' => $avg,
                    'highest_score' => $highest,
                    'lowest_score' => $lowest,
                    'grade_distribution' => $gradeDist,
                    'pass_rate' => $studentAverages->count() > 0
                        ? round($studentAverages->filter(fn($v) => $v >= 50)->count() / $studentAverages->count() * 100, 2) : 0,
                ];

                $allAverages = array_merge($allAverages, $studentAverages->toArray());
            }

            // Sort by average performance descending
            usort($branchComparison, fn($a, $b) => $b['avg_performance'] <=> $a['avg_performance']);

            // Assign rank
            $rank = 1;
            foreach ($branchComparison as &$bc) {
                $bc['rank'] = $rank++;
            }
            unset($bc);

            $overallStats = [
                'total_students' => count($allAverages),
                'total_branches' => count($branchComparison),
                'overall_avg' => count($allAverages) > 0 ? round(array_sum($allAverages) / count($allAverages), 2) : 0,
            ];
        }

        return view('admin.performance-comparison.index', compact(
            'branchComparison', 'overallStats', 'academicYears', 'terms', 'branches',
            'selectedYear', 'selectedTerm'
        ));
    }

    private function getGrade($avg)
    {
        if ($avg <= 0 || $avg === '') return 'I';
        if ($avg >= 80) return 'A';
        if ($avg >= 60) return 'B';
        if ($avg >= 50) return 'C';
        if ($avg >= 40) return 'D';
        return 'F';
    }
}
