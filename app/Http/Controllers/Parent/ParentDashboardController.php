<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\MarkEntry;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;

class ParentDashboardController extends Controller
{
    private function getParent()
    {
        $user = auth()->user();
        if ($user->role !== 'parent') abort(403);

        // Try user_id FK first, then email match
        $parent = ParentModel::where('user_id', $user->id)->first();
        if (!$parent) {
            $parent = ParentModel::where('father_phone', $user->phone)
                ->orWhere('mother_phone', $user->phone)
                ->first();
        }
        if (!$parent) abort(403, 'Parent profile not found.');
        return $parent;
    }

    public function dashboard()
    {
        $parent = $this->getParent();
        $children = $parent->students()->with(['classroom', 'section', 'academicYear'])->where('status', 'active')->get();

        $activeAy = AcademicYear::where('is_current', true)->first();
        $activeTerm = $activeAy ? Term::where('academic_year_id', $activeAy->id)->where('is_active', true)->first() : null;

        // Get latest marks for each child
        $childrenMarks = [];
        foreach ($children as $child) {
            $marks = collect();
            if ($activeAy && $activeTerm) {
                $marks = MarkEntry::with('subject')
                    ->where('student_id', $child->id)
                    ->where('academic_year_id', $activeAy->id)
                    ->where('term_id', $activeTerm->id)
                    ->orderBy('subject_id')
                    ->get();
            }
            $avg = $marks->count() > 0 ? round($marks->avg('grand_total'), 1) : null;

            // Fee info
            $totalFees = 0; $totalPaid = 0;
            try {
                $feePayments = $child->feePayments()->with('fee')->get();
                foreach ($feePayments as $fp) {
                    if ($fp->fee) {
                        $totalFees += $fp->fee->amount;
                    }
                    $totalPaid += $fp->amount_paid;
                }
            } catch (\Throwable $e) {}

            $childrenMarks[$child->id] = [
                'marks' => $marks,
                'average' => $avg,
                'totalFees' => $totalFees,
                'totalPaid' => $totalPaid,
            ];
        }

        return view('parent.dashboard', compact('parent', 'children', 'activeAy', 'activeTerm', 'childrenMarks'));
    }

    public function childMarks(Request $request, $studentId)
    {
        $parent = $this->getParent();

        // Verify this child belongs to this parent
        $student = $parent->students()->where('student_id', $studentId)->where('status', 'active')->first();
        if (!$student) abort(403, 'This student is not linked to your account.');

        $student->load(['classroom', 'section']);

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

        return view('parent.marks', compact('parent', 'student', 'academicYears', 'terms', 'selectedAy', 'selectedTerm', 'marks'));
    }

    public function childProgress($studentId)
    {
        $parent = $this->getParent();
        $student = $parent->students()->where('student_id', $studentId)->where('status', 'active')->first();
        if (!$student) abort(403);
        $student->load(['classroom', 'section']);

        $activeAy = AcademicYear::where('is_current', true)->first();
        $terms = $activeAy ? Term::where('academic_year_id', $activeAy->id)->orderBy('id')->get() : collect();

        $termAverages = [];
        foreach ($terms as $term) {
            $termMarks = MarkEntry::where('student_id', $student->id)
                ->where('academic_year_id', $activeAy->id ?? 0)
                ->where('term_id', $term->id)
                ->get();
            if ($termMarks->count() > 0) {
                $termAverages[] = [
                    'term' => $term,
                    'average' => round($termMarks->avg('grand_total'), 2),
                    'highest' => round($termMarks->max('grand_total'), 2),
                    'lowest' => round($termMarks->min('grand_total'), 2),
                    'count' => $termMarks->count(),
                ];
            }
        }

        return view('parent.progress', compact('parent', 'student', 'terms', 'termAverages', 'activeAy'));
    }

    public function childFees($studentId)
    {
        $parent = $this->getParent();
        $student = $parent->students()->where('student_id', $studentId)->where('status', 'active')->first();
        if (!$student) abort(403);
        $student->load(['classroom']);

        $feePayments = collect();
        $totalFees = 0; $totalPaid = 0; $balance = 0;
        try {
            $feePayments = $student->feePayments()->with(['fee'])->orderBy('created_at', 'desc')->get();
            foreach ($feePayments as $fp) {
                if ($fp->fee) {
                    $totalFees += $fp->fee->amount;
                }
                $totalPaid += $fp->amount_paid;
            }
            $balance = $totalFees - $totalPaid;
        } catch (\Throwable $e) {
            $totalFees = 0; $totalPaid = 0; $balance = 0;
        }

        return view('parent.fees', compact('parent', 'student', 'feePayments', 'totalFees', 'totalPaid', 'balance'));
    }

    public function childProfile($studentId)
    {
        $parent = $this->getParent();
        $student = $parent->students()->where('student_id', $studentId)->where('status', 'active')->first();
        if (!$student) abort(403);
        $student->load(['classroom', 'section', 'academicYear', 'parents']);

        return view('parent.profile', compact('parent', 'student'));
    }
}
