<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\Branch;
use App\Models\FeePayment;
use App\Models\Fee;
use App\Models\Attendance;
use App\Models\MarkEntry;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\IncomeExpense;
use App\Models\ClubActivity;
use App\Models\LessonPlan;
use App\Models\ExamQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GraphicalReportController extends Controller
{
    public function index(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();

        // Student enrollment by branch
        try {
            $studentsByBranch = Branch::withCount('students')->get()->map(fn($b) => [
                'name' => $b->name,
                'count' => $b->students_count,
            ]);
        } catch (\BadMethodCallException $e) {
            // Fallback: manual count if relationship is missing
            $studentsByBranch = Branch::all()->map(fn($b) => [
                'name' => $b->name,
                'count' => Student::where('branch_id', $b->id)->count(),
            ]);
        }

        // Student enrollment by class
        try {
            $studentsByClass = Classroom::withCount('students')->orderBy('numeric_name')->orderBy('name')->get()->map(fn($c) => [
                'name' => $c->name,
                'count' => $c->students_count,
            ]);
        } catch (\BadMethodCallException $e) {
            $studentsByClass = Classroom::orderBy('numeric_name')->orderBy('name')->get()->map(fn($c) => [
                'name' => $c->name,
                'count' => Student::where('class_id', $c->id)->count(),
            ]);
        }

        // Gender distribution
        $genderDist = Student::select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->get()
            ->mapWithKeys(fn($g) => [$g->gender ?? 'Unknown' => $g->count]);

        // Fee collection monthly trend (last 12 months)
        $feeTrend = FeePayment::select(
                DB::raw("DATE_FORMAT(payment_date, '%Y-%m') as month"),
                DB::raw('SUM(amount_paid) as total')
            )
            ->where('payment_date', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn($f) => ['month' => $f->month, 'total' => (float) $f->total]);

        // Fee collection vs expected by term
        $feeByTerm = [];
        if ($currentYear) {
            $terms = Term::where('academic_year_id', $currentYear->id)->get();
            foreach ($terms as $term) {
                $expected = Fee::where('academic_year_id', $currentYear->id)->sum('amount');
                $collected = FeePayment::whereHas('fee', fn($q) => $q->where('academic_year_id', $currentYear->id))
                    ->sum('amount_paid');
                $feeByTerm[] = [
                    'term' => $term->name,
                    'expected' => (float) $expected,
                    'collected' => (float) $collected,
                ];
            }
        }

        // Income vs Expense monthly trend
        $incomeExpenseTrend = IncomeExpense::select(
                'type',
                DB::raw("DATE_FORMAT(date, '%Y-%m') as month"),
                DB::raw('SUM(amount) as total')
            )
            ->where('date', '>=', now()->subMonths(12))
            ->groupBy('type', 'month')
            ->orderBy('month')
            ->get()
            ->groupBy('month')
            ->map(fn($group) => [
                'month' => $group->first()->month,
                'income' => (float) ($group->where('type', 'income')->sum('total') ?: 0),
                'expense' => (float) ($group->where('type', 'expense')->sum('total') ?: 0),
            ])
            ->values();

        // Attendance trend (last 30 days)
        $attendanceTrend = Attendance::select(
                DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as day"),
                DB::raw("SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present"),
                DB::raw("SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent"),
                DB::raw("SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late"),
                DB::raw('COUNT(*) as total')
            )
            ->where('date', '>=', now()->subDays(30))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(fn($a) => [
                'day' => $a->day,
                'present' => (int) $a->present,
                'absent' => (int) $a->absent,
                'late' => (int) $a->late,
                'rate' => $a->total > 0 ? round(($a->present / $a->total) * 100, 1) : 0,
            ]);

        // Performance by class (average marks)
        $performanceByClass = MarkEntry::select('class_id', DB::raw('AVG(grand_total) as avg_total'))
            ->whereNotNull('grand_total')
            ->groupBy('class_id')
            ->with('classRoom')
            ->get()
            ->filter(fn($m) => $m->classRoom)
            ->map(fn($m) => [
                'class' => $m->classRoom->name,
                'average' => round($m->avg_total, 1),
            ])
            ->sortByDesc('average')
            ->values();

        // Performance by subject
        $performanceBySubject = MarkEntry::select('subject_id', DB::raw('AVG(grand_total) as avg_total'))
            ->whereNotNull('grand_total')
            ->groupBy('subject_id')
            ->with('subject')
            ->get()
            ->filter(fn($m) => $m->subject)
            ->map(fn($m) => [
                'subject' => $m->subject->name,
                'average' => round($m->avg_total, 1),
            ])
            ->sortByDesc('average')
            ->take(15)
            ->values();

        // Performance by branch
        try {
            $performanceByBranch = Branch::with(['students.markEntries' => fn($q) => $q->whereNotNull('grand_total')])
                ->get()
                ->map(fn($b) => [
                    'branch' => $b->name,
                    'average' => $b->students->flatMap->markEntries->avg('grand_total') ?? 0,
                ])
                ->filter(fn($b) => $b['average'] > 0)
                ->values();
        } catch (\BadMethodCallException $e) {
            $performanceByBranch = collect();
        }

        // Lesson plan status distribution
        $lessonPlanStatus = LessonPlan::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($l) => [$l->status => $l->count]);

        // Lesson plan by type
        $lessonPlanByType = LessonPlan::select('plan_type', DB::raw('count(*) as count'))
            ->groupBy('plan_type')
            ->get()
            ->mapWithKeys(fn($l) => [$l->plan_type => $l->count]);

        // Exam question approval pipeline
        $examPipeline = ExamQuestion::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($e) => [$e->status => $e->count]);

        // Club activities by status
        $clubActivityStatus = ClubActivity::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($c) => [$c->status => $c->count]);

        // Club activities by type
        $clubActivityType = ClubActivity::select('activity_type', DB::raw('count(*) as count'))
            ->groupBy('activity_type')
            ->get()
            ->mapWithKeys(fn($c) => [$c->activity_type => $c->count]);

        // Student status distribution
        $studentStatus = Student::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($s) => [$s->status => $s->count]);

        // Staff by role
        $staffByRole = DB::table('users')
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->mapWithKeys(fn($u) => [$u->role => $u->count]);

        // Payment method distribution
        $paymentMethods = FeePayment::select('payment_method', DB::raw('count(*) as count'), DB::raw('SUM(amount_paid) as total'))
            ->groupBy('payment_method')
            ->get()
            ->map(fn($p) => [
                'method' => $p->payment_method ?? 'Unknown',
                'count' => $p->count,
                'total' => (float) $p->total,
            ]);

        return view('admin.graphical-reports.index', compact(
            'studentsByBranch', 'studentsByClass', 'genderDist',
            'feeTrend', 'feeByTerm', 'incomeExpenseTrend',
            'attendanceTrend', 'performanceByClass', 'performanceBySubject',
            'performanceByBranch', 'lessonPlanStatus', 'lessonPlanByType',
            'examPipeline', 'clubActivityStatus', 'clubActivityType',
            'studentStatus', 'staffByRole', 'paymentMethods'
        ));
    }
}
