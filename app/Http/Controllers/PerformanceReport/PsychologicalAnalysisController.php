<?php
namespace App\Http\Controllers\PerformanceReport;
use App\Http\Controllers\Controller;
use App\Models\MarkEntry;
use App\Models\PerformanceReport;
use App\Models\ProgressReport;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;

class PsychologicalAnalysisController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $terms = Term::orderBy('id', 'desc')->get();
        $classes = ClassRoom::orderBy('numeric_name')->orderBy('name')->get();
        return view('admin.psychological-analysis.index', compact('academicYears', 'terms', 'classes'));
    }

    public function generate(Request $r)
    {
        $r->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $query = Student::with(['classroom', 'section', 'branch'])
            ->where('class_id', $r->class_id);
        if ($r->filled('section_id')) {
            $query->where('section_id', $r->section_id);
        }
        $students = $query->get();

        $academicYear = AcademicYear::find($r->academic_year_id);
        $term = Term::find($r->term_id);
        $class = ClassRoom::find($r->class_id);

        $analysis = [];
        foreach ($students as $student) {
            $marks = MarkEntry::where('student_id', $student->id)
                ->where('academic_year_id', $r->academic_year_id)
                ->where('term_id', $r->term_id)
                ->get();

            $perfReport = PerformanceReport::where('student_id', $student->id)
                ->where('academic_year_id', $r->academic_year_id)
                ->where('term_id', $r->term_id)
                ->first();

            $progressReport = ProgressReport::where('student_id', $student->id)
                ->where('academic_year_id', $r->academic_year_id)
                ->where('term_id', $r->term_id)
                ->first();

            // Calculate academic metrics
            $avgMark = $marks->count() > 0 ? round($marks->avg('grand_total'), 2) : 0;
            $markTrend = $this->getMarkTrend($student->id, $r->academic_year_id);

            // Psychological profile based on performance patterns
            $profile = $this->buildPsychProfile($student, $marks, $perfReport, $progressReport, $markTrend);

            $analysis[] = [
                'student' => $student,
                'average_mark' => $avgMark,
                'subject_count' => $marks->count(),
                'performance_report' => $perfReport,
                'progress_report' => $progressReport,
                'mark_trend' => $markTrend,
                'psych_profile' => $profile,
            ];
        }

        // Sort by risk level (high risk first)
        usort($analysis, function ($a, $b) {
            $riskOrder = ['high' => 0, 'moderate' => 1, 'low' => 2, 'stable' => 3];
            return ($riskOrder[$a['psych_profile']['risk_level']] ?? 3) <=> ($riskOrder[$b['psych_profile']['risk_level']] ?? 3);
        });

        // Summary stats
        $totalStudents = count($analysis);
        $riskDistribution = ['high' => 0, 'moderate' => 0, 'low' => 0, 'stable' => 0];
        $motivationDistribution = ['highly_motivated' => 0, 'motivated' => 0, 'average' => 0, 'low_motivation' => 0];
        foreach ($analysis as $a) {
            $riskDistribution[$a['psych_profile']['risk_level']]++;
            $motivationDistribution[$a['psych_profile']['motivation_level']]++;
        }

        return view('admin.psychological-analysis.index', compact(
            'analysis', 'academicYear', 'term', 'class', 'totalStudents',
            'riskDistribution', 'motivationDistribution',
            'academicYears', 'terms', 'classes'
        ));
    }

    private function getMarkTrend($studentId, $currentYearId)
    {
        $allProgress = ProgressReport::where('student_id', $studentId)
            ->orderBy('academic_year_id')->orderBy('term_id')->get();
        return $allProgress->map(fn($p) => [
            'term' => ($p->academicYear->name ?? '') . ' - ' . ($p->term->name ?? ''),
            'percentage' => $p->percentage ?? 0,
            'grade' => $p->grade ?? '-',
        ])->toArray();
    }

    private function buildPsychProfile($student, $marks, $perfReport, $progressReport, $markTrend)
    {
        $avgMark = $marks->count() > 0 ? $marks->avg('grand_total') : 0;

        // Determine risk level based on academic performance
        $riskLevel = 'stable';
        if ($avgMark < 40) $riskLevel = 'high';
        elseif ($avgMark < 50) $riskLevel = 'moderate';
        elseif ($avgMark < 60) $riskLevel = 'low';

        // Check declining trend
        $isDeclining = false;
        if (count($markTrend) >= 2) {
            $recent = array_slice($markTrend, -2);
            if ($recent[1]['percentage'] < $recent[0]['percentage']) {
                $isDeclining = true;
                if ($riskLevel === 'low') $riskLevel = 'moderate';
            }
        }

        // Motivation level from performance report
        $motivationLevel = 'average';
        if ($perfReport) {
            $behavior = $perfReport->behavior_rating ?? 5;
            $extracurricular = $perfReport->extracurricular_rating ?? 5;
            $overall = $perfReport->overall_rating ?? 5;
            $score = ($behavior + $extracurricular + $overall) / 3;
            if ($score >= 8) $motivationLevel = 'highly_motivated';
            elseif ($score >= 6) $motivationLevel = 'motivated';
            elseif ($score >= 4) $motivationLevel = 'average';
            else $motivationLevel = 'low_motivation';
        } else {
            if ($avgMark >= 80) $motivationLevel = 'highly_motivated';
            elseif ($avgMark >= 65) $motivationLevel = 'motivated';
            elseif ($avgMark >= 50) $motivationLevel = 'average';
        }

        // Learning style inference from subject variance
        $subjectMarks = $marks->pluck('grand_total')->toArray();
        $variance = count($subjectMarks) > 1 ? $this->variance($subjectMarks) : 0;
        $learningStyle = $variance < 100 ? 'consistent' : ($variance < 300 ? 'moderate_variance' : 'high_variance');

        // Strengths and weaknesses
        $strengths = [];
        $weaknesses = [];
        foreach ($marks as $m) {
            if ($m->subject && $m->grand_total >= 70) $strengths[] = $m->subject->name;
            if ($m->subject && $m->grand_total < 50) $weaknesses[] = $m->subject->name;
        }

        // Recommendations
        $recommendations = [];
        if ($riskLevel === 'high') {
            $recommendations[] = 'Immediate academic counseling recommended';
            $recommendations[] = 'Consider peer tutoring program';
            $recommendations[] = 'Parent-teacher meeting advised';
        } elseif ($riskLevel === 'moderate') {
            $recommendations[] = 'Regular progress monitoring needed';
            $recommendations[] = 'Additional study support recommended';
        }
        if ($isDeclining) {
            $recommendations[] = 'Investigate cause of declining performance';
            $recommendations[] = 'Motivational support may be needed';
        }
        if ($motivationLevel === 'low_motivation') {
            $recommendations[] = 'Psychological counseling recommended';
            $recommendations[] = 'Extracurricular engagement encouraged';
        }
        if (empty($recommendations)) {
            $recommendations[] = 'Continue current academic path';
            $recommendations[] = 'Consider enrichment programs';
        }

        return [
            'risk_level' => $riskLevel,
            'motivation_level' => $motivationLevel,
            'learning_style' => $learningStyle,
            'is_declining' => $isDeclining,
            'strengths' => $strengths,
            'weaknesses' => $weaknesses,
            'recommendations' => $recommendations,
            'behavior_rating' => $perfReport?->behavior_rating,
            'sports_rating' => $perfReport?->sports_rating,
            'extracurricular_rating' => $perfReport?->extracurricular_rating,
        ];
    }

    private function variance($arr)
    {
        $mean = array_sum($arr) / count($arr);
        $squaredDiffs = array_map(fn($v) => pow($v - $mean, 2), $arr);
        return array_sum($squaredDiffs) / count($arr);
    }
}
