<?php

namespace App\Http\Controllers\Performance;

use App\Http\Controllers\Controller;
use App\Models\MarkEntry;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\Branch;
use App\Models\Term;
use App\Models\AcademicYear;
use App\Models\Subject;
use Illuminate\Http\Request;

class PerformanceAnalysisController extends Controller
{
    /**
     * Dashboard with overall school performance metrics.
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $terms = Term::orderBy('id', 'desc')->get();

        $currentYear = AcademicYear::where('is_current', true)->first();
        $currentTerm = $terms->first();

        // Overall school stats
        $totalStudents = Student::where('status', 'active')->count();
        $totalBranches = Branch::where('is_active', true)->count();
        $totalClasses = Classroom::count();

        // Compute overall average from mark entries
        $overallAvg = 0;
        $passRate = 0;
        $topPerformers = collect();
        $atRiskCount = 0;
        $genderStats = [];

        if ($currentYear && $currentTerm) {
            $marks = MarkEntry::where('academic_year_id', $currentYear->id)
                ->where('term_id', $currentTerm->id)
                ->with(['student', 'subject'])
                ->get();

            $grouped = $marks->groupBy('student_id');
            $studentAverages = [];

            foreach ($grouped as $studentId => $entries) {
                $count = $entries->count();
                if ($count > 0) {
                    $avg = round($entries->avg('grand_total'), 2);
                    $studentAverages[$studentId] = $avg;
                }
            }

            $allAvgs = array_values($studentAverages);

            if (count($allAvgs) > 0) {
                $overallAvg = round(array_sum($allAvgs) / count($allAvgs), 2);
                $passing = count(array_filter($allAvgs, fn($a) => $a >= 50));
                $passRate = round(($passing / count($allAvgs)) * 100, 2);
                $atRiskCount = count(array_filter($allAvgs, fn($a) => $a < 50));
            }

            // Top performers
            arsort($studentAverages);
            $topIds = array_slice(array_keys($studentAverages), 0, 10, true);
            $topPerformers = Student::whereIn('id', $topIds)->get()->map(function ($student) use ($studentAverages) {
                $student->performance_avg = $studentAverages[$student->id] ?? 0;
                $student->performance_grade = $this->getGrade($student->performance_avg);
                return $student;
            })->sortByDesc('performance_avg');

            // Gender analysis
            $maleAverages = [];
            $femaleAverages = [];
            foreach ($studentAverages as $sid => $avg) {
                $student = Student::find($sid);
                if ($student) {
                    if (strtolower($student->gender) === 'male' || strtolower($student->gender) === 'm') {
                        $maleAverages[] = $avg;
                    } elseif (strtolower($student->gender) === 'female' || strtolower($student->gender) === 'f') {
                        $femaleAverages[] = $avg;
                    }
                }
            }

            $genderStats = [
                'male' => [
                    'count' => count($maleAverages),
                    'average' => count($maleAverages) > 0 ? round(array_sum($maleAverages) / count($maleAverages), 2) : 0,
                    'pass_rate' => count($maleAverages) > 0 ? round(count(array_filter($maleAverages, fn($a) => $a >= 50)) / count($maleAverages) * 100, 2) : 0,
                ],
                'female' => [
                    'count' => count($femaleAverages),
                    'average' => count($femaleAverages) > 0 ? round(array_sum($femaleAverages) / count($femaleAverages), 2) : 0,
                    'pass_rate' => count($femaleAverages) > 0 ? round(count(array_filter($femaleAverages, fn($a) => $a >= 50)) / count($femaleAverages) * 100, 2) : 0,
                ],
            ];
        }

        // At-risk students (average < 50)
        $atRiskStudents = $this->getAtRiskStudents($currentYear, $currentTerm, 10);

        return view('admin.performance.index', compact(
            'academicYears', 'terms', 'currentYear', 'currentTerm',
            'totalStudents', 'totalBranches', 'totalClasses',
            'overallAvg', 'passRate', 'topPerformers', 'atRiskCount',
            'atRiskStudents', 'genderStats'
        ));
    }

    /**
     * Individual student deep analysis with suggestions.
     */
    public function studentAnalysis($id)
    {
        $student = Student::with(['classroom', 'section', 'branch', 'markEntries.subject', 'markEntries.term'])
            ->findOrFail($id);

        $markEntries = $student->markEntries()->with(['subject', 'term'])->get();

        // Group by term for trend analysis
        $byTerm = $markEntries->groupBy('term_id');

        $termAnalysis = [];
        $allSubjectScores = [];

        foreach ($byTerm as $termId => $entries) {
            $term = $entries->first()->term;
            $subjectScores = [];

            foreach ($entries as $entry) {
                if ($entry->subject) {
                    $score = floatval($entry->grand_total ?? 0);
                    $subjectScores[] = [
                        'subject' => $entry->subject,
                        'subject_id' => $entry->subject_id,
                        'ca_total' => floatval($entry->ca_total ?? 0),
                        'exam_total' => floatval($entry->exam_total ?? 0),
                        'grand_total' => $score,
                        'grade' => $this->getGrade($score),
                    ];
                    $allSubjectScores[$entry->subject_id][] = $score;
                }
            }

            $avg = count($subjectScores) > 0
                ? round(array_sum(array_column($subjectScores, 'grand_total')) / count($subjectScores), 2)
                : 0;

            $termAnalysis[] = [
                'term' => $term,
                'term_id' => $termId,
                'average' => $avg,
                'grade' => $this->getGrade($avg),
                'subjects' => $subjectScores,
                'total' => array_sum(array_column($subjectScores, 'grand_total')),
                'subject_count' => count($subjectScores),
            ];
        }

        // Sort terms by id
        usort($termAnalysis, fn($a, $b) => $a['term_id'] <=> $b['term_id']);

        // Overall average
        $overallAvg = count($termAnalysis) > 0
            ? round(array_sum(array_column($termAnalysis, 'average')) / count($termAnalysis), 2)
            : 0;

        // Strengths and weaknesses
        $strengths = [];
        $weaknesses = [];

        foreach ($allSubjectScores as $subjectId => $scores) {
            $subject = Subject::find($subjectId);
            if ($subject && count($scores) > 0) {
                $avgScore = round(array_sum($scores) / count($scores), 2);
                $entry = ['subject' => $subject, 'average' => $avgScore];

                if ($avgScore >= 70) {
                    $strengths[] = $entry;
                } elseif ($avgScore < 50) {
                    $weaknesses[] = $entry;
                }
            }
        }

        // Sort strengths desc, weaknesses asc
        usort($strengths, fn($a, $b) => $b['average'] <=> $a['average']);
        usort($weaknesses, fn($a, $b) => $a['average'] <=> $b['average']);

        // Generate suggestions
        $suggestions = $this->generateSuggestions($overallAvg, $allSubjectScores);

        // Latest term subjects for detailed breakdown
        $latestTerm = count($termAnalysis) > 0 ? $termAnalysis[count($termAnalysis) - 1] : null;

        return view('admin.performance.student-analysis', compact(
            'student', 'termAnalysis', 'overallAvg', 'strengths', 'weaknesses',
            'suggestions', 'latestTerm', 'markEntries'
        ));
    }

    /**
     * Compare performance across classes.
     */
    public function classComparison(Request $request)
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $terms = Term::orderBy('id', 'desc')->get();
        $classes = Classroom::orderBy('numeric_name')->orderBy('name')->get();

        $selectedYear = $request->filled('academic_year_id')
            ? AcademicYear::find($request->academic_year_id)
            : AcademicYear::where('is_current', true)->first();

        $selectedTerm = $request->filled('term_id')
            ? Term::find($request->term_id)
            : $terms->first();

        $classComparison = [];
        $overallStats = ['total_students' => 0, 'total_classes' => 0, 'overall_avg' => 0];

        if ($selectedYear && $selectedTerm) {
            $allAverages = [];

            foreach ($classes as $class) {
                $studentIds = Student::where('class_id', $class->id)
                    ->where('status', 'active')
                    ->pluck('id');

                $marks = MarkEntry::whereIn('student_id', $studentIds)
                    ->where('academic_year_id', $selectedYear->id)
                    ->where('term_id', $selectedTerm->id)
                    ->get();

                $studentAvgs = $marks->groupBy('student_id')->map(function ($entries) {
                    $count = $entries->count();
                    return $count > 0 ? round($entries->avg('grand_total'), 2) : 0;
                })->filter(fn($v) => $v > 0);

                $avg = $studentAvgs->count() > 0 ? round($studentAvgs->avg(), 2) : 0;
                $highest = $studentAvgs->count() > 0 ? round($studentAvgs->max(), 2) : 0;
                $lowest = $studentAvgs->count() > 0 ? round($studentAvgs->min(), 2) : 0;

                $passRate = $studentAvgs->count() > 0
                    ? round($studentAvgs->filter(fn($v) => $v >= 50)->count() / $studentAvgs->count() * 100, 2)
                    : 0;

                // Grade distribution
                $gradeDist = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0, 'I' => 0];
                foreach ($studentAvgs as $sa) {
                    $g = $this->getGrade($sa);
                    $gradeDist[$g] = ($gradeDist[$g] ?? 0) + 1;
                }

                $classComparison[] = [
                    'class' => $class,
                    'student_count' => $studentIds->count(),
                    'avg_performance' => $avg,
                    'highest_score' => $highest,
                    'lowest_score' => $lowest,
                    'pass_rate' => $passRate,
                    'grade_distribution' => $gradeDist,
                ];

                $allAverages = array_merge($allAverages, $studentAvgs->toArray());
            }

            // Sort by average descending
            usort($classComparison, fn($a, $b) => $b['avg_performance'] <=> $a['avg_performance']);

            // Assign ranks
            $rank = 1;
            foreach ($classComparison as &$cc) {
                $cc['rank'] = $rank++;
            }
            unset($cc);

            $overallStats = [
                'total_students' => count($allAverages),
                'total_classes' => count($classComparison),
                'overall_avg' => count($allAverages) > 0 ? round(array_sum($allAverages) / count($allAverages), 2) : 0,
            ];
        }

        return view('admin.performance.class-comparison', compact(
            'classComparison', 'overallStats', 'academicYears', 'terms', 'classes',
            'selectedYear', 'selectedTerm'
        ));
    }

    /**
     * Compare performance across branches.
     */
    public function branchComparison(Request $request)
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $terms = Term::orderBy('id', 'desc')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();

        $selectedYear = $request->filled('academic_year_id')
            ? AcademicYear::find($request->academic_year_id)
            : AcademicYear::where('is_current', true)->first();

        $selectedTerm = $request->filled('term_id')
            ? Term::find($request->term_id)
            : $terms->first();

        $branchComparison = [];
        $overallStats = ['total_students' => 0, 'total_branches' => 0, 'overall_avg' => 0];

        if ($selectedYear && $selectedTerm) {
            $allAverages = [];

            foreach ($branches as $branch) {
                $studentIds = Student::where('branch_id', $branch->id)
                    ->where('status', 'active')
                    ->pluck('id');

                $marks = MarkEntry::whereIn('student_id', $studentIds)
                    ->where('academic_year_id', $selectedYear->id)
                    ->where('term_id', $selectedTerm->id)
                    ->get();

                $studentAvgs = $marks->groupBy('student_id')->map(function ($entries) {
                    $count = $entries->count();
                    return $count > 0 ? round($entries->avg('grand_total'), 2) : 0;
                })->filter(fn($v) => $v > 0);

                $avg = $studentAvgs->count() > 0 ? round($studentAvgs->avg(), 2) : 0;
                $highest = $studentAvgs->count() > 0 ? round($studentAvgs->max(), 2) : 0;
                $lowest = $studentAvgs->count() > 0 ? round($studentAvgs->min(), 2) : 0;

                $passRate = $studentAvgs->count() > 0
                    ? round($studentAvgs->filter(fn($v) => $v >= 50)->count() / $studentAvgs->count() * 100, 2)
                    : 0;

                // Grade distribution
                $gradeDist = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0, 'I' => 0];
                foreach ($studentAvgs as $sa) {
                    $g = $this->getGrade($sa);
                    $gradeDist[$g] = ($gradeDist[$g] ?? 0) + 1;
                }

                // Subject-level performance for this branch
                $subjectPerf = $marks->groupBy('subject_id')->map(function ($entries) {
                    $subj = $entries->first()->subject;
                    $avg = round($entries->avg('grand_total'), 2);
                    return ['subject' => $subj, 'average' => $avg];
                })->sortByDesc('average')->values()->toArray();

                $branchComparison[] = [
                    'branch' => $branch,
                    'student_count' => $studentIds->count(),
                    'avg_performance' => $avg,
                    'highest_score' => $highest,
                    'lowest_score' => $lowest,
                    'pass_rate' => $passRate,
                    'grade_distribution' => $gradeDist,
                    'subject_performance' => $subjectPerf,
                ];

                $allAverages = array_merge($allAverages, $studentAvgs->toArray());
            }

            // Sort by average descending
            usort($branchComparison, fn($a, $b) => $b['avg_performance'] <=> $a['avg_performance']);

            // Assign ranks
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

        return view('admin.performance.branch-comparison', compact(
            'branchComparison', 'overallStats', 'academicYears', 'terms', 'branches',
            'selectedYear', 'selectedTerm'
        ));
    }

    /**
     * Performance analysis by gender.
     */
    public function genderAnalysis(Request $request)
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $terms = Term::orderBy('id', 'desc')->get();

        $selectedYear = $request->filled('academic_year_id')
            ? AcademicYear::find($request->academic_year_id)
            : AcademicYear::where('is_current', true)->first();

        $selectedTerm = $request->filled('term_id')
            ? Term::find($request->term_id)
            : $terms->first();

        $genderData = [];
        $subjectGenderData = [];

        if ($selectedYear && $selectedTerm) {
            $marks = MarkEntry::where('academic_year_id', $selectedYear->id)
                ->where('term_id', $selectedTerm->id)
                ->with(['student', 'subject'])
                ->get();

            $groupedByStudent = $marks->groupBy('student_id');
            $maleAverages = [];
            $femaleAverages = [];
            $maleGradeDist = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0, 'I' => 0];
            $femaleGradeDist = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0, 'I' => 0];

            // Subject-level gender data
            $subjectGenderMap = [];

            foreach ($groupedByStudent as $studentId => $entries) {
                $student = Student::find($studentId);
                if (!$student) continue;

                $count = $entries->count();
                $avg = $count > 0 ? round($entries->avg('grand_total'), 2) : 0;
                if ($avg <= 0) continue;

                $grade = $this->getGrade($avg);

                if (strtolower($student->gender) === 'male' || strtolower($student->gender) === 'm') {
                    $maleAverages[] = $avg;
                    $maleGradeDist[$grade] = ($maleGradeDist[$grade] ?? 0) + 1;
                } elseif (strtolower($student->gender) === 'female' || strtolower($student->gender) === 'f') {
                    $femaleAverages[] = $avg;
                    $femaleGradeDist[$grade] = ($femaleGradeDist[$grade] ?? 0) + 1;
                }

                // Subject-level breakdown
                foreach ($entries as $entry) {
                    if (!$entry->subject) continue;
                    $subjId = $entry->subject_id;
                    $score = floatval($entry->grand_total ?? 0);

                    if (!isset($subjectGenderMap[$subjId])) {
                        $subjectGenderMap[$subjId] = [
                            'subject' => $entry->subject,
                            'male_scores' => [],
                            'female_scores' => [],
                        ];
                    }

                    if (strtolower($student->gender) === 'male' || strtolower($student->gender) === 'm') {
                        $subjectGenderMap[$subjId]['male_scores'][] = $score;
                    } elseif (strtolower($student->gender) === 'female' || strtolower($student->gender) === 'f') {
                        $subjectGenderMap[$subjId]['female_scores'][] = $score;
                    }
                }
            }

            // Build subject gender comparison
            foreach ($subjectGenderMap as $subjId => $data) {
                $maleAvg = count($data['male_scores']) > 0 ? round(array_sum($data['male_scores']) / count($data['male_scores']), 2) : 0;
                $femaleAvg = count($data['female_scores']) > 0 ? round(array_sum($data['female_scores']) / count($data['female_scores']), 2) : 0;
                $subjectGenderData[] = [
                    'subject' => $data['subject'],
                    'male_avg' => $maleAvg,
                    'female_avg' => $femaleAvg,
                    'male_count' => count($data['male_scores']),
                    'female_count' => count($data['female_scores']),
                    'difference' => round($femaleAvg - $maleAvg, 2),
                ];
            }

            usort($subjectGenderData, fn($a, $b) => abs($b['difference']) <=> abs($a['difference']));

            $genderData = [
                'male' => [
                    'count' => count($maleAverages),
                    'average' => count($maleAverages) > 0 ? round(array_sum($maleAverages) / count($maleAverages), 2) : 0,
                    'highest' => count($maleAverages) > 0 ? round(max($maleAverages), 2) : 0,
                    'lowest' => count($maleAverages) > 0 ? round(min($maleAverages), 2) : 0,
                    'pass_rate' => count($maleAverages) > 0 ? round(count(array_filter($maleAverages, fn($a) => $a >= 50)) / count($maleAverages) * 100, 2) : 0,
                    'grade_distribution' => $maleGradeDist,
                ],
                'female' => [
                    'count' => count($femaleAverages),
                    'average' => count($femaleAverages) > 0 ? round(array_sum($femaleAverages) / count($femaleAverages), 2) : 0,
                    'highest' => count($femaleAverages) > 0 ? round(max($femaleAverages), 2) : 0,
                    'lowest' => count($femaleAverages) > 0 ? round(min($femaleAverages), 2) : 0,
                    'pass_rate' => count($femaleAverages) > 0 ? round(count(array_filter($femaleAverages, fn($a) => $a >= 50)) / count($femaleAverages) * 100, 2) : 0,
                    'grade_distribution' => $femaleGradeDist,
                ],
            ];
        }

        return view('admin.performance.gender-analysis', compact(
            'genderData', 'subjectGenderData', 'academicYears', 'terms',
            'selectedYear', 'selectedTerm'
        ));
    }

    /**
     * Identify students at risk of failing.
     */
    public function atRiskStudents(Request $request)
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $terms = Term::orderBy('id', 'desc')->get();

        $selectedYear = $request->filled('academic_year_id')
            ? AcademicYear::find($request->academic_year_id)
            : AcademicYear::where('is_current', true)->first();

        $selectedTerm = $request->filled('term_id')
            ? Term::find($request->term_id)
            : $terms->first();

        $threshold = $request->get('threshold', 50);
        $atRiskStudents = $this->getAtRiskStudents($selectedYear, $selectedTerm, null, $threshold);

        $riskLevels = [
            'critical' => 0,
            'warning' => 0,
            'improvement' => 0,
        ];

        foreach ($atRiskStudents as $s) {
            if ($s['average'] < 30) {
                $riskLevels['critical']++;
            } elseif ($s['average'] < 40) {
                $riskLevels['warning']++;
            } else {
                $riskLevels['improvement']++;
            }
        }

        return view('admin.performance.at-risk', compact(
            'atRiskStudents', 'academicYears', 'terms',
            'selectedYear', 'selectedTerm', 'threshold', 'riskLevels'
        ));
    }

    /**
     * AI-like performance suggestions for a student.
     */
    public function suggestions($id)
    {
        $student = Student::with(['classroom', 'section', 'branch'])->findOrFail($id);
        $markEntries = $student->markEntries()->with(['subject', 'term'])->get();

        // Compute overall average
        $byTerm = $markEntries->groupBy('term_id');
        $allSubjectScores = [];
        $termAverages = [];

        foreach ($byTerm as $termId => $entries) {
            $subjectScores = [];
            foreach ($entries as $entry) {
                if ($entry->subject) {
                    $score = floatval($entry->grand_total ?? 0);
                    $subjectScores[] = $score;
                    $allSubjectScores[$entry->subject_id][] = $score;
                }
            }
            if (count($subjectScores) > 0) {
                $termAverages[] = round(array_sum($subjectScores) / count($subjectScores), 2);
            }
        }

        $overallAvg = count($termAverages) > 0
            ? round(array_sum($termAverages) / count($termAverages), 2)
            : 0;

        $suggestions = $this->generateSuggestions($overallAvg, $allSubjectScores);

        // Trend analysis
        $trend = 'stable';
        if (count($termAverages) >= 2) {
            $first = $termAverages[0];
            $last = $termAverages[count($termAverages) - 1];
            $diff = $last - $first;
            if ($diff > 5) $trend = 'improving';
            elseif ($diff < -5) $trend = 'declining';
        }

        return view('admin.performance.suggestions', compact(
            'student', 'suggestions', 'overallAvg', 'trend', 'termAverages'
        ));
    }

    /**
     * Get at-risk students based on average performance below threshold.
     */
    private function getAtRiskStudents($academicYear, $term, $limit = null, $threshold = 50)
    {
        if (!$academicYear || !$term) {
            return [];
        }

        $marks = MarkEntry::where('academic_year_id', $academicYear->id)
            ->where('term_id', $term->id)
            ->with(['student', 'subject'])
            ->get();

        $grouped = $marks->groupBy('student_id');
        $atRisk = [];

        foreach ($grouped as $studentId => $entries) {
            $count = $entries->count();
            if ($count === 0) continue;

            $avg = round($entries->avg('grand_total'), 2);

            if ($avg < $threshold) {
                $student = $entries->first()->student;
                if (!$student) continue;

                // Find weakest subjects
                $subjectScores = [];
                foreach ($entries as $entry) {
                    if ($entry->subject) {
                        $subjectScores[] = [
                            'subject' => $entry->subject,
                            'score' => floatval($entry->grand_total ?? 0),
                        ];
                    }
                }
                usort($subjectScores, fn($a, $b) => $a['score'] <=> $b['score']);

                $riskLevel = 'improvement';
                if ($avg < 30) $riskLevel = 'critical';
                elseif ($avg < 40) $riskLevel = 'warning';

                $atRisk[] = [
                    'student' => $student,
                    'average' => $avg,
                    'risk_level' => $riskLevel,
                    'weak_subjects' => array_slice($subjectScores, 0, 3),
                    'subject_count' => $count,
                ];
            }
        }

        // Sort by average ascending (most at-risk first)
        usort($atRisk, fn($a, $b) => $a['average'] <=> $b['average']);

        if ($limit) {
            $atRisk = array_slice($atRisk, 0, $limit);
        }

        return $atRisk;
    }

    /**
     * Generate AI-like performance suggestions.
     */
    private function generateSuggestions($overallAvg, $allSubjectScores)
    {
        $suggestions = [];

        // Overall suggestion
        if ($overallAvg < 50) {
            $suggestions['overall'] = [
                'level' => 'critical',
                'icon' => 'fas fa-exclamation-triangle',
                'title' => 'Critical: Student is at risk',
                'message' => 'Immediate intervention needed. Recommend tutoring and parent-teacher conference.',
                'actions' => [
                    'Schedule a parent-teacher conference immediately',
                    'Assign a peer tutor or after-school support',
                    'Create a structured daily study plan',
                    'Monitor progress weekly',
                ],
            ];
        } elseif ($overallAvg < 60) {
            $suggestions['overall'] = [
                'level' => 'warning',
                'icon' => 'fas fa-exclamation-circle',
                'title' => 'Warning: Student is below average',
                'message' => 'Additional support and study time recommended.',
                'actions' => [
                    'Increase daily study time by 30 minutes',
                    'Focus on understanding core concepts',
                    'Seek help from teachers during office hours',
                    'Form study groups with stronger peers',
                ],
            ];
        } elseif ($overallAvg < 70) {
            $suggestions['overall'] = [
                'level' => 'improvement',
                'icon' => 'fas fa-arrow-up',
                'title' => 'Improvement needed',
                'message' => 'Focus on weak subjects. Regular practice recommended.',
                'actions' => [
                    'Identify and target weak areas for improvement',
                    'Practice with past exam papers',
                    'Maintain consistent study schedule',
                    'Set specific improvement goals for each subject',
                ],
            ];
        } elseif ($overallAvg < 80) {
            $suggestions['overall'] = [
                'level' => 'good',
                'icon' => 'fas fa-thumbs-up',
                'title' => 'Good performance',
                'message' => 'Encourage consistency and aim higher.',
                'actions' => [
                    'Challenge yourself with advanced problems',
                    'Help classmates to reinforce your own learning',
                    'Explore enrichment materials',
                    'Set goals to reach the next performance level',
                ],
            ];
        } elseif ($overallAvg < 90) {
            $suggestions['overall'] = [
                'level' => 'very_good',
                'icon' => 'fas fa-star',
                'title' => 'Very good!',
                'message' => 'Student has strong potential. Consider advanced challenges.',
                'actions' => [
                    'Explore advanced or honors-level coursework',
                    'Participate in academic competitions',
                    'Mentor peers who need help',
                    'Develop leadership skills through group projects',
                ],
            ];
        } else {
            $suggestions['overall'] = [
                'level' => 'excellent',
                'icon' => 'fas fa-trophy',
                'title' => 'Excellent performance!',
                'message' => 'Student is a high achiever. Consider leadership roles and mentoring.',
                'actions' => [
                    'Take on leadership roles in academic activities',
                    'Mentor fellow students',
                    'Pursue independent research projects',
                    'Apply for scholarships and academic recognition',
                ],
            ];
        }

        // Subject-specific suggestions
        $suggestions['subjects'] = [];

        foreach ($allSubjectScores as $subjectId => $scores) {
            $subject = Subject::find($subjectId);
            if (!$subject || count($scores) === 0) continue;

            $avgScore = round(array_sum($scores) / count($scores), 2);
            $subjectSuggestion = [];

            if ($avgScore < 40) {
                $subjectSuggestion = [
                    'subject' => $subject,
                    'average' => $avgScore,
                    'level' => 'critical',
                    'message' => "Severely struggling in {$subject->name}. Urgent remedial classes needed.",
                    'actions' => [
                        "Attend remedial classes for {$subject->name}",
                        'Complete all homework and practice exercises',
                        'Seek one-on-one tutoring',
                        'Review fundamentals daily',
                    ],
                ];
            } elseif ($avgScore < 50) {
                $subjectSuggestion = [
                    'subject' => $subject,
                    'average' => $avgScore,
                    'level' => 'warning',
                    'message' => "Below passing in {$subject->name}. Extra study time and teacher support needed.",
                    'actions' => [
                        "Dedicate extra daily study time to {$subject->name}",
                        'Ask teachers for additional practice materials',
                        'Form a study group for this subject',
                        'Review and correct past mistakes',
                    ],
                ];
            } elseif ($avgScore < 60) {
                $subjectSuggestion = [
                    'subject' => $subject,
                    'average' => $avgScore,
                    'level' => 'improvement',
                    'message' => "Below average in {$subject->name}. Focused practice recommended.",
                    'actions' => [
                        "Practice {$subject->name} problems daily",
                        'Focus on understanding key concepts',
                        'Attend all classes and take detailed notes',
                        'Test yourself with practice quizzes',
                    ],
                ];
            } elseif ($avgScore < 70) {
                $subjectSuggestion = [
                    'subject' => $subject,
                    'average' => $avgScore,
                    'level' => 'moderate',
                    'message' => "Decent in {$subject->name}, but room for improvement.",
                    'actions' => [
                        "Work on weak topics within {$subject->name}",
                        'Challenge yourself with harder problems',
                        'Review mistakes from past assessments',
                        'Seek feedback from the teacher',
                    ],
                ];
            } elseif ($avgScore < 80) {
                $subjectSuggestion = [
                    'subject' => $subject,
                    'average' => $avgScore,
                    'level' => 'good',
                    'message' => "Good performance in {$subject->name}. Keep it up and push for excellence.",
                    'actions' => [
                        'Continue current study habits',
                        'Explore advanced topics',
                        'Help classmates who are struggling',
                        'Aim for the top scores in class',
                    ],
                ];
            } else {
                $subjectSuggestion = [
                    'subject' => $subject,
                    'average' => $avgScore,
                    'level' => 'excellent',
                    'message' => "Excellent in {$subject->name}! A clear strength.",
                    'actions' => [
                        'Consider advanced coursework in this area',
                        'Participate in subject competitions',
                        'Mentor peers in this subject',
                        'Explore related enrichment activities',
                    ],
                ];
            }

            $suggestions['subjects'][] = $subjectSuggestion;
        }

        // Sort subjects by average ascending (weakest first)
        usort($suggestions['subjects'], fn($a, $b) => $a['average'] <=> $b['average']);

        return $suggestions;
    }

    /**
     * Get grade from average score.
     */
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
