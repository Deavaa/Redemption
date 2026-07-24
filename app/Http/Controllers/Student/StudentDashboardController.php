<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\MarkEntry;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    private function getStudent()
    {
        $user = auth()->user();
        if ($user->role !== 'student') abort(403);

        // Try user_id FK first, then email match
        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            $student = Student::where('email', $user->email)->first();
        }
        if (!$student) abort(403, 'Student profile not found.');
        return $student;
    }

    public function dashboard()
    {
        $student = $this->getStudent();
        $student->load(['classroom', 'section', 'academicYear']);

        // Get active academic year and term
        $activeAy = AcademicYear::where('is_current', true)->first();
        $activeTerm = $activeAy ? Term::where('academic_year_id', $activeAy->id)->where('is_active', true)->first() : null;

        // Get latest marks for active term
        $latestMarks = collect();
        if ($activeAy && $activeTerm) {
            $latestMarks = MarkEntry::with(['subject', 'term'])
                ->where('student_id', $student->id)
                ->where('academic_year_id', $activeAy->id)
                ->where('term_id', $activeTerm->id)
                ->orderBy('subject_id')
                ->get();
        }

        // Calculate average score
        $averageScore = $latestMarks->count() > 0 ? round($latestMarks->avg('grand_total'), 1) : 0;

        // Get fee info (using actual FeePayment columns)
        $totalFees = 0;
        $totalPaid = 0;
        try {
            $feePayments = $student->feePayments()->with('fee')->get();
            $totalPaid = $feePayments->sum('amount_paid');
            $totalFees = $feePayments->map(function ($fp) {
                return $fp->fee ? $fp->fee->amount : 0;
            })->sum();
        } catch (\Throwable $e) {}

        $feeBalance = $totalFees - $totalPaid;

        return view('student.dashboard', compact('student', 'activeAy', 'activeTerm', 'latestMarks', 'averageScore', 'totalFees', 'totalPaid', 'feeBalance'));
    }

    public function marks(Request $request)
    {
        $student = $this->getStudent();

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $activeAy = AcademicYear::where('is_current', true)->first();
        $selectedAy = $request->filled('academic_year_id') ? AcademicYear::find($request->academic_year_id) : $activeAy;

        $terms = $selectedAy ? Term::where('academic_year_id', $selectedAy->id)->orderBy('id')->get() : collect();
        $selectedTerm = $request->filled('term_id') ? Term::find($request->term_id) : $terms->first();

        $marks = collect();
        if ($selectedAy && $selectedTerm) {
            $marks = MarkEntry::with(['subject', 'term', 'academicYear', 'classRoom', 'section'])
                ->where('student_id', $student->id)
                ->where('academic_year_id', $selectedAy->id)
                ->where('term_id', $selectedTerm->id)
                ->orderByRaw('(SELECT priority FROM subjects WHERE subjects.id = mark_entries.subject_id) ASC')
                ->get();
        }

        return view('student.marks', compact('student', 'academicYears', 'terms', 'selectedAy', 'selectedTerm', 'marks'));
    }

    /**
     * Check if ranks are published for a given term.
     */
    private function ranksPublished($termId): bool
    {
        if (!$termId) return false;
        $term = Term::find($termId);
        return $term ? (bool) $term->ranks_published : false;
    }

    public function progress()
    {
        $student = $this->getStudent();
        $student->load(['classroom', 'section']);

        // Get all terms with marks for trend analysis
        $activeAy = AcademicYear::where('is_current', true)->first();
        $terms = $activeAy ? Term::where('academic_year_id', $activeAy->id)->orderBy('id')->get() : collect();

        $termAverages = [];
        foreach ($terms as $term) {
            $termMarks = MarkEntry::where('student_id', $student->id)
                ->where('academic_year_id', $activeAy->id ?? 0)
                ->where('term_id', $term->id)
                ->get();

            if ($termMarks->count() > 0) {
                $avg = $termMarks->avg('grand_total');
                $termAverages[] = [
                    'term' => $term,
                    'average' => round($avg, 2),
                    'highest' => round($termMarks->max('grand_total'), 2),
                    'lowest' => round($termMarks->min('grand_total'), 2),
                    'count' => $termMarks->count(),
                ];
            }
        }

        // Progress reports if they exist
        $progressReports = collect();
        try {
            $progressReports = $student->progressReports()->orderBy('created_at', 'desc')->get();
        } catch (\Throwable $e) {}

        return view('student.progress', compact('student', 'terms', 'termAverages', 'progressReports', 'activeAy'));
    }

    public function fees()
    {
        $student = $this->getStudent();
        $student->load(['classroom']);

        $feePayments = collect();
        try {
            $feePayments = $student->feePayments()->with(['fee'])->orderBy('created_at', 'desc')->get();
            $totalPaid = $feePayments->sum('amount_paid');
            $totalFees = $feePayments->map(function ($fp) {
                return $fp->fee ? $fp->fee->amount : 0;
            })->sum();
            $balance = $totalFees - $totalPaid;
        } catch (\Throwable $e) {
            $totalFees = 0;
            $totalPaid = 0;
            $balance = 0;
        }

        return view('student.fees', compact('student', 'feePayments', 'totalFees', 'totalPaid', 'balance'));
    }

    public function profile()
    {
        $student = $this->getStudent();
        $student->load(['classroom', 'section', 'academicYear', 'parents']);

        return view('student.profile', compact('student'));
    }
}
