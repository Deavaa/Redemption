<?php

namespace App\Http\Controllers\LessonPlan;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\LessonPlan;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LessonPlanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isTeacher = $user->role === 'teacher';

        // Get teacher model for teacher users
        $teacherModel = null;
        if ($isTeacher) {
            $teacherModel = Teacher::where('user_id', $user->id)
                ->orWhere('email', $user->email)->first();
        }

        // Base query
        $query = LessonPlan::with(['subject', 'classRoom', 'section', 'academicYear', 'term', 'teacher', 'followUps']);

        // Teacher sees only their own plans
        if ($isTeacher && $teacherModel) {
            $query->where('teacher_id', $teacherModel->id);
        }

        // Filters
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('objectives', 'LIKE', "%{$search}%");
            });
        }

        $lessonPlans = $query->latestFirst()->paginate(20);

        // Stats
        $statsQuery = $isTeacher && $teacherModel
            ? LessonPlan::where('teacher_id', $teacherModel->id)
            : new LessonPlan;

        $totalPlans   = (clone $statsQuery)->count();
        $draftCount   = (clone $statsQuery)->where('status', 'draft')->count();
        $submittedCount = (clone $statsQuery)->where('status', 'submitted')->count();
        $approvedCount  = (clone $statsQuery)->where('status', 'approved')->count();

        // Filter dropdowns
        $academicYears = AcademicYear::orderBy('name')->get();
        $terms = Term::when($request->filled('academic_year_id'),
            fn($q) => $q->where('academic_year_id', $request->academic_year_id)
        )->orderBy('name')->get();
        $classes = ClassRoom::orderBy('name')->get();
        $subjects = Subject::active()->ordered()->get();

        // Teacher assignments for teacher users (for cascading dropdowns)
        $teacherAssignments = [];
        if ($isTeacher && $teacherModel) {
            $teacherAssignments = TeacherAssignment::where('teacher_id', $teacherModel->id)
                ->with(['subject', 'classRoom', 'section', 'academicYear', 'term'])
                ->get()
                ->toArray();
        }

        return view('admin.lesson-plans.index', compact(
            'lessonPlans', 'isTeacher', 'teacherModel',
            'totalPlans', 'draftCount', 'submittedCount', 'approvedCount',
            'academicYears', 'terms', 'classes', 'subjects',
            'teacherAssignments'
        ));
    }

    public function create()
    {
        $user = Auth::user();
        $isTeacher = $user->role === 'teacher';
        $teacherModel = null;
        $teacherAssignments = [];

        if ($isTeacher) {
            $teacherModel = Teacher::where('user_id', $user->id)
                ->orWhere('email', $user->email)->first();
            $teacherAssignments = TeacherAssignment::where('teacher_id', $teacherModel?->id)
                ->with(['subject', 'classRoom', 'section', 'academicYear', 'term'])
                ->get()->toArray();
        }

        $academicYears = AcademicYear::orderBy('name')->get();
        $terms = Term::orderBy('name')->get();
        $classes = ClassRoom::orderBy('name')->get();
        $subjects = Subject::active()->ordered()->get();
        $teachers = $isTeacher ? collect() : Teacher::orderBy('full_name')->get();
        $currentAy = AcademicYear::where('is_current', true)->first();
        $currentTerm = $currentAy ? Term::where('academic_year_id', $currentAy->id)->orderBy('name')->first() : null;

        return view('admin.lesson-plans.create', compact(
            'isTeacher', 'teacherModel', 'teacherAssignments',
            'academicYears', 'terms', 'classes', 'subjects', 'teachers',
            'currentAy', 'currentTerm'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isTeacher = $user->role === 'teacher';

        $rules = [
            'subject_id'        => 'required|exists:subjects,id',
            'class_id'          => 'required|exists:classes,id',
            'section_id'        => 'nullable|exists:sections,id',
            'academic_year_id'  => 'required|exists:academic_years,id',
            'term_id'           => 'required|exists:terms,id',
            'title'             => 'required|string|max:255',
            'plan_type'         => 'nullable|in:daily,weekly,yearly',
            'week_start_date'   => 'nullable|date',
            'week_end_date'     => 'nullable|date',
            'daily_breakdown'   => 'nullable|json',
            'yearly_overview'   => 'nullable|string',
            'term_goals'        => 'nullable|json',
            'objectives'        => 'nullable|string',
            'materials'         => 'nullable|string',
            'activities'        => 'nullable|string',
            'assessment'        => 'nullable|string',
            'homework'          => 'nullable|string',
            'notes'             => 'nullable|string',
            'week_number'       => 'nullable|integer|min:1',
            'lesson_date'       => 'nullable|date',
            'duration_minutes'  => 'nullable|integer|min:1',
            'status'            => 'nullable|in:draft,submitted',
        ];

        // Admin selects teacher; teacher is auto-assigned
        if (!$isTeacher) {
            $rules['teacher_id'] = 'required|exists:teachers,id';
        }

        $data = $request->validate($rules);

        // Auto-assign teacher
        if ($isTeacher) {
            $teacherModel = Teacher::where('user_id', $user->id)
                ->orWhere('email', $user->email)->first();
            $data['teacher_id'] = $teacherModel?->id;
        }

        $data['week_number']      = $data['week_number'] ?? 1;
        $data['duration_minutes'] = $data['duration_minutes'] ?? 45;
        $data['status']           = $data['status'] ?? 'draft';
        $data['plan_type']        = $data['plan_type'] ?? 'daily';
        if (isset($data['daily_breakdown']) && is_string($data['daily_breakdown'])) {
            $data['daily_breakdown'] = json_decode($data['daily_breakdown'], true);
        }
        if (isset($data['term_goals']) && is_string($data['term_goals'])) {
            $data['term_goals'] = json_decode($data['term_goals'], true);
        }

        LessonPlan::create($data);

        return redirect()->route('admin.lesson-plans.index')
            ->with('success', 'Lesson plan created successfully.');
    }

    public function show(LessonPlan $lessonPlan)
    {
        $lessonPlan->load([
            'subject', 'classRoom', 'section', 'academicYear', 'term',
            'teacher', 'reviewer', 'followUps.followedUpBy'
        ]);

        return view('admin.lesson-plans.show', compact('lessonPlan'));
    }

    public function edit(LessonPlan $lessonPlan)
    {
        $user = Auth::user();
        $isTeacher = $user->role === 'teacher';

        // Teacher can only edit their own drafts or revision plans
        if ($isTeacher) {
            $teacherModel = Teacher::where('user_id', $user->id)
                ->orWhere('email', $user->email)->first();
            if ($teacherModel && $lessonPlan->teacher_id !== $teacherModel->id) {
                abort(403, 'You can only edit your own lesson plans.');
            }
            if (!in_array($lessonPlan->status, ['draft', 'revision'])) {
                abort(403, 'You can only edit draft or revision lesson plans.');
            }
        }

        $teacherAssignments = [];
        if ($isTeacher) {
            $teacherModel = Teacher::where('user_id', $user->id)
                ->orWhere('email', $user->email)->first();
            $teacherAssignments = TeacherAssignment::where('teacher_id', $teacherModel?->id)
                ->with(['subject', 'classRoom', 'section', 'academicYear', 'term'])
                ->get()->toArray();
        }

        $academicYears = AcademicYear::orderBy('name')->get();
        $terms = Term::where('academic_year_id', $lessonPlan->academic_year_id)->orderBy('name')->get();
        $classes = ClassRoom::orderBy('name')->get();
        $subjects = Subject::active()->ordered()->get();
        $teachers = $isTeacher ? collect() : Teacher::orderBy('full_name')->get();

        return view('admin.lesson-plans.edit', compact(
            'lessonPlan', 'isTeacher', 'teacherAssignments',
            'academicYears', 'terms', 'classes', 'subjects', 'teachers'
        ));
    }

    public function update(Request $request, LessonPlan $lessonPlan)
    {
        $user = Auth::user();
        $isTeacher = $user->role === 'teacher';

        $rules = [
            'subject_id'        => 'required|exists:subjects,id',
            'class_id'          => 'required|exists:classes,id',
            'section_id'        => 'nullable|exists:sections,id',
            'academic_year_id'  => 'required|exists:academic_years,id',
            'term_id'           => 'required|exists:terms,id',
            'title'             => 'required|string|max:255',
            'plan_type'         => 'nullable|in:daily,weekly,yearly',
            'week_start_date'   => 'nullable|date',
            'week_end_date'     => 'nullable|date',
            'daily_breakdown'   => 'nullable|json',
            'yearly_overview'   => 'nullable|string',
            'term_goals'        => 'nullable|json',
            'objectives'        => 'nullable|string',
            'materials'         => 'nullable|string',
            'activities'        => 'nullable|string',
            'assessment'        => 'nullable|string',
            'homework'          => 'nullable|string',
            'notes'             => 'nullable|string',
            'week_number'       => 'nullable|integer|min:1',
            'lesson_date'       => 'nullable|date',
            'duration_minutes'  => 'nullable|integer|min:1',
            'status'            => 'nullable|in:draft,submitted',
        ];

        if (!$isTeacher) {
            $rules['teacher_id'] = 'required|exists:teachers,id';
        }

        $data = $request->validate($rules);

        if (isset($data['daily_breakdown']) && is_string($data['daily_breakdown'])) {
            $data['daily_breakdown'] = json_decode($data['daily_breakdown'], true);
        }
        if (isset($data['term_goals']) && is_string($data['term_goals'])) {
            $data['term_goals'] = json_decode($data['term_goals'], true);
        }

        $lessonPlan->update($data);

        return redirect()->route('admin.lesson-plans.index')
            ->with('success', 'Lesson plan updated successfully.');
    }

    /**
     * Review / approve / request revision — used by admin, branch_principal, general_manager
     */
    public function review(Request $request, LessonPlan $lessonPlan)
    {
        $request->validate([
            'status'           => 'required|in:reviewed,approved,revision',
            'reviewer_comment' => 'nullable|string',
        ]);

        $lessonPlan->update([
            'status'           => $request->status,
            'reviewer_comment' => $request->reviewer_comment,
            'reviewed_by'      => Auth::id(),
            'reviewed_at'      => now(),
        ]);

        $statusLabel = LessonPlan::statusOptions()[$request->status] ?? $request->status;

        return back()->with('success', "Lesson plan marked as {$statusLabel}.");
    }

    /**
     * Department head reviews a lesson plan.
     */
    public function departmentReview(Request $request, LessonPlan $lessonPlan)
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && !$user->hasRole('department_head')) {
            abort(403, 'Only department heads or admins can perform department reviews.');
        }

        $request->validate([
            'department_head_status' => 'required|in:reviewed,approved,revision',
            'department_head_comment' => 'nullable|string|max:2000',
        ]);

        $lessonPlan->update([
            'department_head_status' => $request->department_head_status,
            'department_head_comment' => $request->department_head_comment,
            'department_head_id' => $user->id,
            'department_head_reviewed_at' => now(),
            'status' => $request->department_head_status === 'approved' ? 'reviewed' : $request->department_head_status,
        ]);

        $label = LessonPlan::departmentHeadStatusOptions()[$request->department_head_status] ?? $request->department_head_status;
        return back()->with('success', "Department review: {$label}");
    }

    /**
     * Principal reviews a lesson plan.
     */
    public function principalReview(Request $request, LessonPlan $lessonPlan)
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && !$user->hasRole('branch_principal')) {
            abort(403, 'Only branch principals or admins can perform principal reviews.');
        }

        $request->validate([
            'principal_status' => 'required|in:reviewed,approved,revision',
            'principal_comment' => 'nullable|string|max:2000',
        ]);

        $lessonPlan->update([
            'principal_status' => $request->principal_status,
            'principal_comment' => $request->principal_comment,
            'principal_reviewed_id' => $user->id,
            'principal_reviewed_at' => now(),
            'status' => $request->principal_status === 'approved' ? 'approved' : $request->principal_status,
        ]);

        $label = LessonPlan::principalStatusOptions()[$request->principal_status] ?? $request->principal_status;
        return back()->with('success', "Principal review: {$label}");
    }

    public function destroy(LessonPlan $lessonPlan)
    {
        $user = Auth::user();
        $isTeacher = $user->role === 'teacher';

        if ($isTeacher) {
            $teacherModel = Teacher::where('user_id', $user->id)
                ->orWhere('email', $user->email)->first();
            if ($teacherModel && $lessonPlan->teacher_id !== $teacherModel->id) {
                abort(403, 'You can only delete your own lesson plans.');
            }
            if (!in_array($lessonPlan->status, ['draft', 'revision'])) {
                abort(403, 'You can only delete draft or revision lesson plans.');
            }
        }

        $lessonPlan->delete();
        return back()->with('success', 'Lesson plan deleted.');
    }
}
