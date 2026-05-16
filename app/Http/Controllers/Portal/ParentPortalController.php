<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\MarkEntry;
use App\Models\FeePayment;
use App\Models\Fee;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentPortalController extends Controller
{
    /**
     * Get the authenticated parent's record with children.
     */
    private function getParent()
    {
        $user = Auth::user();
        return ParentModel::where('user_id', $user->id)
            ->with(['students.classroom', 'students.section', 'students.academicYear', 'students.branch'])
            ->first();
    }

    /**
     * Parent dashboard — overview of all children.
     */
    public function dashboard()
    {
        $parent = $this->getParent();

        if (!$parent) {
            return redirect()->route('login')->with('error', 'Parent profile not found.');
        }

        $currentAy = AcademicYear::where('is_current', true)->first();
        $activeTerm = $currentAy
            ? Term::where('academic_year_id', $currentAy->id)->where('is_active', true)->first()
            : null;

        $childrenData = [];
        foreach ($parent->students as $student) {
            // Recent marks summary
            $marks = MarkEntry::where('student_id', $student->id)
                ->with(['subject', 'term'])
                ->when($currentAy, fn($q) => $q->where('academic_year_id', $currentAy->id))
                ->when($activeTerm, fn($q) => $q->where('term_id', $activeTerm->id))
                ->get();

            // Fee summary
            $totalFees = Fee::where('class_id', $student->class_id)
                ->when($currentAy, fn($q) => $q->where('academic_year_id', $currentAy->id))
                ->sum('amount');
            $totalPaid = FeePayment::where('student_id', $student->id)
                ->when($currentAy, fn($q) => $q->where('academic_year_id', $currentAy->id))
                ->sum('amount_paid');

            $childrenData[] = [
                'student' => $student,
                'marks' => $marks,
                'average' => $marks->avg('grand_total'),
                'totalFees' => $totalFees,
                'totalPaid' => $totalPaid,
                'balance' => max(0, $totalFees - $totalPaid),
            ];
        }

        return view('portal.parent.dashboard', compact('parent', 'childrenData', 'currentAy', 'activeTerm'));
    }

    /**
     * View a specific child's marks.
     */
    public function childMarks(Request $request, $studentId)
    {
        $parent = $this->getParent();

        if (!$parent) {
            return redirect()->route('login')->with('error', 'Parent profile not found.');
        }

        // Verify this student belongs to this parent
        $student = $parent->students()->where('students.id', $studentId)->first();
        if (!$student) {
            abort(403, 'You do not have access to this student\'s information.');
        }

        $student->load(['classroom', 'section', 'academicYear']);

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

        $marksByTerm = $marks->groupBy('term_id');

        return view('portal.parent.child-marks', compact(
            'parent', 'student', 'academicYears', 'terms', 'marks', 'marksByTerm',
            'selectedAyId', 'selectedTermId', 'currentAy'
        ));
    }

    /**
     * View a specific child's fee information.
     */
    public function childFees($studentId)
    {
        $parent = $this->getParent();

        if (!$parent) {
            return redirect()->route('login')->with('error', 'Parent profile not found.');
        }

        $student = $parent->students()->where('students.id', $studentId)->first();
        if (!$student) {
            abort(403, 'You do not have access to this student\'s information.');
        }

        $student->load(['classroom', 'section', 'academicYear']);
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

        return view('portal.parent.child-fees', compact(
            'parent', 'student', 'feeStructures', 'payments',
            'totalFees', 'totalPaid', 'balance', 'currentAy'
        ));
    }

    /**
     * View a specific child's profile.
     */
    public function childProfile($studentId)
    {
        $parent = $this->getParent();

        if (!$parent) {
            return redirect()->route('login')->with('error', 'Parent profile not found.');
        }

        $student = $parent->students()->where('students.id', $studentId)->first();
        if (!$student) {
            abort(403, 'You do not have access to this student\'s information.');
        }

        $student->load(['classroom', 'section', 'academicYear', 'branch', 'parents']);

        return view('portal.parent.child-profile', compact('parent', 'student'));
    }
}
