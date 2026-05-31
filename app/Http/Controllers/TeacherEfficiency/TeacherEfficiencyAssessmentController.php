<?php

namespace App\Http\Controllers\TeacherEfficiency;

use App\Http\Controllers\Controller;
use App\Models\TeacherEfficiencyAssessment;
use App\Models\Teacher;
use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherEfficiencyAssessmentController extends Controller
{
    public function index(Request $request)
    {
        $query = TeacherEfficiencyAssessment::with(['teacher', 'assessor', 'academicYear', 'term', 'branch']);

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }
        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('grade')) {
            $query->where('grade', $request->grade);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Branch principals only see their branch
        if (Auth::user()->role === 'branch_principal') {
            $query->where('branch_id', Auth::user()->branch_id);
        }

        $assessments = $query->latestFirst()->paginate(15)->withQueryString();

        // Stats
        $statsQuery = TeacherEfficiencyAssessment::when(
            Auth::user()->role === 'branch_principal',
            fn($q) => $q->where('branch_id', Auth::user()->branch_id)
        );
        $allAssessments = (clone $statsQuery)->where('status', '!=', 'draft')->get();

        $stats = [
            'total'             => $allAssessments->count(),
            'excellent'         => $allAssessments->where('grade', 'excellent')->count(),
            'good'              => $allAssessments->where('grade', 'good')->count(),
            'satisfactory'      => $allAssessments->where('grade', 'satisfactory')->count(),
            'needs_improvement' => $allAssessments->where('grade', 'needs_improvement')->count(),
            'unsatisfactory'    => $allAssessments->where('grade', 'unsatisfactory')->count(),
            'avg_score'         => $allAssessments->avg('overall_score') ? round($allAssessments->avg('overall_score'), 1) : 0,
        ];

        $teachers = Teacher::where('status', 'active')->orderBy('full_name')->get();
        $allTerms = Term::orderBy('id')->get();
        $branches = Branch::orderBy('name')->get();

        return view('admin.teacher-efficiency.index', compact('assessments', 'stats', 'teachers', 'allTerms', 'branches'));
    }

    public function create()
    {
        $user = Auth::user();

        // Only principals and admin can create
        if (!in_array($user->role, ['admin', 'super_admin', 'branch_principal', 'general_manager'])) {
            abort(403, 'Only principals and administrators can create assessments.');
        }

        $academicYears = AcademicYear::orderByDesc('id')->get();
        $allTerms = Term::orderBy('id')->get();
        $branches = Branch::orderBy('name')->get();

        // Auto-select principal's branch
        $selectedBranchId = $user->role === 'branch_principal' ? $user->branch_id : null;

        // Load teachers for the selected branch
        $teachers = $selectedBranchId
            ? Teacher::where('branch_id', $selectedBranchId)->where('status', 'active')->orderBy('full_name')->get()
            : Teacher::where('status', 'active')->orderBy('full_name')->get();

        return view('admin.teacher-efficiency.create', compact('teachers', 'academicYears', 'allTerms', 'branches', 'selectedBranchId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id'           => 'required|exists:teachers,id',
            'academic_year_id'     => 'required|exists:academic_years,id',
            'term_id'              => 'required|exists:terms,id',
            'branch_id'            => 'required|exists:branches,id',
            'lesson_delivery'      => 'required|integer|min:1|max:5',
            'student_assessment'   => 'required|integer|min:1|max:5',
            'curriculum_coverage'  => 'required|integer|min:1|max:5',
            'classroom_environment'=> 'required|integer|min:1|max:5',
            'student_participation'=> 'required|integer|min:1|max:5',
            'professional_development' => 'required|integer|min:1|max:5',
            'communication'        => 'required|integer|min:1|max:5',
            'time_management'      => 'required|integer|min:1|max:5',
            'collaboration'        => 'required|integer|min:1|max:5',
            'result_achievement'   => 'required|integer|min:1|max:5',
            'strengths'            => 'nullable|string',
            'areas_for_improvement'=> 'nullable|string',
            'action_plan'          => 'nullable|string',
            'comments'             => 'nullable|string',
            'status'               => 'in:draft,completed',
        ]);

        // Check unique constraint
        $exists = TeacherEfficiencyAssessment::where('teacher_id', $validated['teacher_id'])
            ->where('term_id', $validated['term_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['teacher_id' => 'An assessment already exists for this teacher in the selected term and academic year.']);
        }

        $validated['assessor_id'] = Auth::id();
        $validated['overall_score'] = TeacherEfficiencyAssessment::calculateOverallScore($validated);
        $validated['grade'] = TeacherEfficiencyAssessment::scoreToGrade($validated['overall_score']);

        if (!isset($validated['status'])) {
            $validated['status'] = 'completed';
        }

        TeacherEfficiencyAssessment::create($validated);

        return redirect()->route('admin.teacher-efficiency.index')
            ->with('success', 'Teacher efficiency assessment created successfully.');
    }

    public function show(TeacherEfficiencyAssessment $teacherEfficiencyAssessment)
    {
        $teacherEfficiencyAssessment->load(['teacher', 'assessor', 'academicYear', 'term', 'branch']);
        $criteriaScores = $teacherEfficiencyAssessment->getCriteriaScores();

        return view('admin.teacher-efficiency.show', compact('teacherEfficiencyAssessment', 'criteriaScores'));
    }

    public function edit(TeacherEfficiencyAssessment $teacherEfficiencyAssessment)
    {
        if ($teacherEfficiencyAssessment->status !== 'draft') {
            return redirect()->route('admin.teacher-efficiency.show', $teacherEfficiencyAssessment)
                ->withErrors('Only draft assessments can be edited.');
        }

        if ($teacherEfficiencyAssessment->is_locked) {
            return redirect()->route('admin.teacher-efficiency.show', $teacherEfficiencyAssessment)
                ->withErrors('This assessment is locked and cannot be edited.');
        }

        $academicYears = AcademicYear::orderByDesc('id')->get();
        $allTerms = Term::orderBy('id')->get();
        $branches = Branch::orderBy('name')->get();

        $selectedBranchId = $teacherEfficiencyAssessment->branch_id;
        $teachers = $selectedBranchId
            ? Teacher::where('branch_id', $selectedBranchId)->where('status', 'active')->orderBy('full_name')->get()
            : Teacher::where('status', 'active')->orderBy('full_name')->get();

        return view('admin.teacher-efficiency.edit', compact('teacherEfficiencyAssessment', 'teachers', 'academicYears', 'allTerms', 'branches', 'selectedBranchId'));
    }

    public function update(Request $request, TeacherEfficiencyAssessment $teacherEfficiencyAssessment)
    {
        if ($teacherEfficiencyAssessment->status !== 'draft' || $teacherEfficiencyAssessment->is_locked) {
            return redirect()->route('admin.teacher-efficiency.show', $teacherEfficiencyAssessment)
                ->withErrors('Only unlocked draft assessments can be updated.');
        }

        $validated = $request->validate([
            'teacher_id'           => 'required|exists:teachers,id',
            'academic_year_id'     => 'required|exists:academic_years,id',
            'term_id'              => 'required|exists:terms,id',
            'branch_id'            => 'required|exists:branches,id',
            'lesson_delivery'      => 'required|integer|min:1|max:5',
            'student_assessment'   => 'required|integer|min:1|max:5',
            'curriculum_coverage'  => 'required|integer|min:1|max:5',
            'classroom_environment'=> 'required|integer|min:1|max:5',
            'student_participation'=> 'required|integer|min:1|max:5',
            'professional_development' => 'required|integer|min:1|max:5',
            'communication'        => 'required|integer|min:1|max:5',
            'time_management'      => 'required|integer|min:1|max:5',
            'collaboration'        => 'required|integer|min:1|max:5',
            'result_achievement'   => 'required|integer|min:1|max:5',
            'strengths'            => 'nullable|string',
            'areas_for_improvement'=> 'nullable|string',
            'action_plan'          => 'nullable|string',
            'comments'             => 'nullable|string',
            'status'               => 'in:draft,completed',
        ]);

        // Check unique constraint (excluding current)
        $exists = TeacherEfficiencyAssessment::where('teacher_id', $validated['teacher_id'])
            ->where('term_id', $validated['term_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('id', '!=', $teacherEfficiencyAssessment->id)
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['teacher_id' => 'An assessment already exists for this teacher in the selected term and academic year.']);
        }

        $validated['overall_score'] = TeacherEfficiencyAssessment::calculateOverallScore($validated);
        $validated['grade'] = TeacherEfficiencyAssessment::scoreToGrade($validated['overall_score']);

        $teacherEfficiencyAssessment->update($validated);

        return redirect()->route('admin.teacher-efficiency.index')
            ->with('success', 'Assessment updated successfully.');
    }

    public function destroy(TeacherEfficiencyAssessment $teacherEfficiencyAssessment)
    {
        if ($teacherEfficiencyAssessment->status !== 'draft') {
            return redirect()->route('admin.teacher-efficiency.index')
                ->withErrors('Only draft assessments can be deleted.');
        }

        $teacherEfficiencyAssessment->delete();

        return redirect()->route('admin.teacher-efficiency.index')
            ->with('success', 'Assessment deleted.');
    }

    public function acknowledge(TeacherEfficiencyAssessment $teacherEfficiencyAssessment)
    {
        if ($teacherEfficiencyAssessment->status !== 'completed') {
            return back()->withErrors('Only completed assessments can be acknowledged.');
        }

        $teacherEfficiencyAssessment->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
        ]);

        return redirect()->route('admin.teacher-efficiency.show', $teacherEfficiencyAssessment)
            ->with('success', 'Assessment acknowledged successfully.');
    }

    public function lock(TeacherEfficiencyAssessment $teacherEfficiencyAssessment)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'super_admin', 'branch_principal'])) {
            abort(403, 'Only administrators and principals can lock assessments.');
        }

        $teacherEfficiencyAssessment->update(['is_locked' => true]);

        return redirect()->route('admin.teacher-efficiency.show', $teacherEfficiencyAssessment)
            ->with('success', 'Assessment locked. The teacher can only view it now.');
    }

    public function summary(Request $request)
    {
        $query = TeacherEfficiencyAssessment::with(['teacher', 'term', 'branch', 'academicYear'])
            ->where('status', '!=', 'draft');

        if (Auth::user()->role === 'branch_principal') {
            $query->where('branch_id', Auth::user()->branch_id);
        }

        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        $assessments = $query->get();

        // Teacher ranking by average score
        $teacherRanking = $assessments->groupBy('teacher_id')->map(function ($items, $teacherId) {
            $teacher = $items->first()->teacher;
            return [
                'teacher_id'   => $teacherId,
                'teacher_name' => $teacher ? $teacher->full_name : 'Unknown',
                'avg_score'    => round($items->avg('overall_score'), 1),
                'assessment_count' => $items->count(),
                'latest_grade' => $items->sortByDesc('created_at')->first()->grade_label ?? 'N/A',
            ];
        })->sortByDesc('avg_score')->values();

        // Grade distribution
        $gradeDistribution = [
            'excellent'        => $assessments->where('grade', 'excellent')->count(),
            'good'             => $assessments->where('grade', 'good')->count(),
            'satisfactory'     => $assessments->where('grade', 'satisfactory')->count(),
            'needs_improvement'=> $assessments->where('grade', 'needs_improvement')->count(),
            'unsatisfactory'   => $assessments->where('grade', 'unsatisfactory')->count(),
        ];

        // Per-criteria average
        $criteriaAverages = [];
        foreach (TeacherEfficiencyAssessment::CRITERIA as $field => $label) {
            $criteriaAverages[$field] = [
                'label'  => $label,
                'average'=> round($assessments->avg($field), 2),
            ];
        }

        $allTerms = Term::orderBy('id')->get();
        $branches = Branch::orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('id')->get();

        return view('admin.teacher-efficiency.summary', compact(
            'teacherRanking', 'gradeDistribution', 'criteriaAverages',
            'allTerms', 'branches', 'academicYears', 'assessments'
        ));
    }

    public function apiTeachersByBranch(Request $request)
    {
        $branchId = $request->get('branch_id');

        $teachers = Teacher::where('status', 'active')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'branch_id']);

        return response()->json($teachers);
    }
}
