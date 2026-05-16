<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\MarkEntry;
use App\Models\FeePayment;
use App\Models\Fee;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentPortalController extends Controller
{
    /**
     * Get the authenticated student's record.
     */
    private function getStudent()
    {
        $user = Auth::user();
        return Student::where('user_id', $user->id)
            ->with(['classroom', 'section', 'academicYear', 'branch'])
            ->first();
    }

    /**
     * Student dashboard — overview of their info.
     */
    public function dashboard()
    {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        $currentAy = AcademicYear::where('is_current', true)->first();
        $activeTerm = $currentAy
            ? Term::where('academic_year_id', $currentAy->id)->where('is_active', true)->first()
            : null;

        // Recent marks summary
        $recentMarks = MarkEntry::where('student_id', $student->id)
            ->with(['subject', 'term', 'academicYear'])
            ->when($currentAy, fn($q) => $q->where('academic_year_id', $currentAy->id))
            ->when($activeTerm, fn($q) => $q->where('term_id', $activeTerm->id))
            ->orderBy('subject_id')
            ->get();

        // Fee summary
        $totalFees = Fee::where('class_id', $student->class_id)
            ->when($currentAy, fn($q) => $q->where('academic_year_id', $currentAy->id))
            ->sum('amount');
        $totalPaid = FeePayment::where('student_id', $student->id)
            ->when($currentAy, fn($q) => $q->where('academic_year_id', $currentAy->id))
            ->sum('amount_paid');
        $balance = max(0, $totalFees - $totalPaid);

        return view('portal.student.dashboard', compact(
            'student', 'currentAy', 'activeTerm', 'recentMarks', 'totalFees', 'totalPaid', 'balance'
        ));
    }

    /**
     * Student's marks/progress for all terms.
     */
    public function marks(Request $request)
    {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $currentAy = AcademicYear::where('is_current', true)->first();
        $selectedAyId = $request->input('academic_year_id', $currentAy?->id);
        $selectedTermId = $request->input('term_id');

        $terms = $selectedAyId
            ? Term::where('academic_year_id', $selectedAyId)->orderBy('id')->get()
            : collect();

        $marks = MarkEntry::where('student_id', $student->id)
            ->with(['subject', 'term', 'academicYear'])
            ->when($selectedAyId, fn($q) => $q->where('academic_year_id', $selectedAyId))
            ->when($selectedTermId, fn($q) => $q->where('term_id', $selectedTermId))
            ->orderByRaw('(SELECT priority FROM subjects WHERE subjects.id = mark_entries.subject_id) ASC')
            ->get();

        // Group by term for display
        $marksByTerm = $marks->groupBy('term_id');

        return view('portal.student.marks', compact(
            'student', 'academicYears', 'terms', 'marks', 'marksByTerm',
            'selectedAyId', 'selectedTermId', 'currentAy'
        ));
    }

    /**
     * Student's fee payment history.
     */
    public function fees()
    {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        $currentAy = AcademicYear::where('is_current', true)->first();

        $feeStructures = Fee::where('class_id', $student->class_id)
            ->when($currentAy, fn($q) => $q->where('academic_year_id', $currentAy->id))
            ->with(['academicYear', 'term'])
            ->orderBy('id')
            ->get();

        $payments = FeePayment::where('student_id', $student->id)
            ->with(['academicYear', 'term'])
            ->when($currentAy, fn($q) => $q->where('academic_year_id', $currentAy->id))
            ->orderByDesc('payment_date')
            ->get();

        $totalFees = $feeStructures->sum('amount');
        $totalPaid = $payments->sum('amount_paid');
        $balance = max(0, $totalFees - $totalPaid);

        return view('portal.student.fees', compact(
            'student', 'feeStructures', 'payments', 'totalFees', 'totalPaid', 'balance', 'currentAy'
        ));
    }

    /**
     * Student profile information.
     */
    public function profile()
    {
        $student = $this->getStudent();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        $student->load(['classroom', 'section', 'academicYear', 'branch', 'parents']);

        return view('portal.student.profile', compact('student'));
    }
}
