<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Classroom;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExamQuestionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = ExamQuestion::with(['teacher', 'subject', 'classRoom', 'section', 'exam', 'academicYear', 'term', 'branch', 'departmentHead', 'principal']);

        // Access control: teachers see own, dept heads see pending for their subject, principals see pending_principal, admin sees all
        if ($user->role === 'teacher') {
            $teacherProfile = $user->teacherProfile;
            if ($teacherProfile) {
                $query->where('teacher_id', $teacherProfile->id);
            } else {
                $query->whereRaw('1 = 0'); // No teacher profile, show nothing
            }
        } elseif ($user->hasRole('branch_principal')) {
            // Branch principals see pending_principal + their own branch questions
            $query->where(function ($q) use ($user) {
                $q->where('status', 'pending_principal');
                if ($user->branch_id) {
                    $q->orWhere('branch_id', $user->branch_id);
                } else {
                    $q->orWhereNull('branch_id');
                }
            });
        } elseif ($user->hasRole('department_head')) {
            // Department heads see pending_department questions for subjects they oversee + their own
            $teacherProfile = $user->teacherProfile;
            if ($teacherProfile) {
                $query->where(function ($q) use ($teacherProfile) {
                    $q->where('status', 'pending_department')
                      ->orWhere('teacher_id', $teacherProfile->id);
                });
            }
        }
        // Admin sees all (no filter)

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('exam_id')) {
            $query->where('exam_id', $request->exam_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $examQuestions = $query->latestFirst()->paginate(20);

        // Stats
        $baseQuery = ExamQuestion::query();
        if ($user->role === 'teacher') {
            $teacherProfile = $user->teacherProfile;
            if ($teacherProfile) {
                $baseQuery->where('teacher_id', $teacherProfile->id);
            }
        }

        $totalQuestions = (clone $baseQuery)->count();
        $pendingDepartment = (clone $baseQuery)->where('status', 'pending_department')->count();
        $pendingPrincipal = (clone $baseQuery)->where('status', 'pending_principal')->count();
        $approved = (clone $baseQuery)->where('status', 'approved')->count();
        $rejected = (clone $baseQuery)->whereIn('status', ['rejected_by_department', 'rejected_by_principal'])->count();
        $revisionCount = (clone $baseQuery)->where('status', 'revision')->count();

        // Filter dropdowns
        $subjects = Subject::orderBy('name')->get();
        $classes = Classroom::orderBy('numeric_name')->orderBy('name')->get();
        $exams = Exam::orderByDesc('start_date')->limit(50)->get();
        $branches = Branch::orderBy('name')->get();

        $canManage = $user->role === 'admin'
            || $user->hasRole('branch_principal')
            || $user->hasRole('department_head')
            || $user->role === 'teacher';

        $canReview = $user->role === 'admin'
            || $user->hasRole('branch_principal')
            || $user->hasRole('department_head');

        return view('admin.exam-questions.index', compact(
            'examQuestions', 'totalQuestions', 'pendingDepartment', 'pendingPrincipal',
            'approved', 'rejected', 'revisionCount', 'subjects', 'classes', 'exams',
            'branches', 'canManage', 'canReview'
        ));
    }

    public function create()
    {
        $user = auth()->user();

        $subjects = Subject::orderBy('name')->get();
        $classes = Classroom::orderBy('numeric_name')->orderBy('name')->get();
        $exams = Exam::orderByDesc('start_date')->get();
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $terms = Term::orderBy('id')->get();
        $branches = Branch::orderBy('name')->get();
        $sections = collect();

        // Get teacher profile for auto-selection
        $teacherProfile = $user->teacherProfile;

        // Get teacher's assigned subjects and classes for auto-fill
        $teacherAssignments = collect();
        $teacherSubjectIds = [];
        $teacherClassIds = [];
        if ($teacherProfile) {
            $teacherAssignments = \App\Models\TeacherAssignment::where('teacher_id', $teacherProfile->id)
                ->with(['subject', 'classRoom'])
                ->get();
            $teacherSubjectIds = $teacherAssignments->pluck('subject_id')->unique()->values()->toArray();
            $teacherClassIds = $teacherAssignments->pluck('class_id')->unique()->values()->toArray();
        }

        // Get current active academic year
        $activeAcademicYear = AcademicYear::where('is_current', 1)->first();

        return view('admin.exam-questions.create', compact(
            'subjects', 'classes', 'exams', 'academicYears', 'terms', 'branches',
            'sections', 'teacherProfile', 'teacherAssignments', 'teacherSubjectIds',
            'teacherClassIds', 'activeAcademicYear'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:500',
            'subject_id'        => 'required|exists:subjects,id',
            'class_id'          => 'required|exists:classes,id',
            'section_id'        => 'nullable|exists:sections,id',
            'exam_id'           => 'nullable|exists:exams,id',
            'academic_year_id'  => 'nullable|exists:academic_years,id',
            'term_id'           => 'nullable|exists:terms,id',
            'branch_id'         => 'nullable|exists:branches,id',
            'description'       => 'nullable|string|max:5000',
            'questions'         => 'required|string',
            'question_type'     => 'required|in:multiple_choice,essay,short_answer,mixed',
            'total_marks'       => 'required|integer|min:1',
            'duration_minutes'  => 'nullable|integer|min:1',
        ]);

        $user = auth()->user();
        $teacherProfile = $user->teacherProfile;

        if (!$teacherProfile) {
            return back()->withInput()->with('error', 'No teacher profile found for your account.');
        }

        $validated['teacher_id'] = $teacherProfile->id;
        $validated['status'] = 'pending_department';

        // Auto-fill branch if not provided
        if (empty($validated['branch_id'])) {
            $validated['branch_id'] = $user->branch_id;
        }

        // Auto-fill academic year and term if exam is selected
        if (!empty($validated['exam_id'])) {
            $exam = Exam::find($validated['exam_id']);
            if ($exam) {
                if (empty($validated['academic_year_id'])) {
                    $validated['academic_year_id'] = $exam->academic_year_id;
                }
                if (empty($validated['term_id'])) {
                    $validated['term_id'] = $exam->term_id;
                }
            }
        }

        // Handle file attachment
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $validated['attachment'] = $file->storeAs('exam_questions', $filename, 'public');
        }

        try {
            ExamQuestion::create($validated);

            return redirect()->route('admin.exam-questions.index')
                ->with('success', 'Exam questions submitted successfully. Awaiting department head review.');
        } catch (\Exception $e) {
            Log::error('ExamQuestion store failed', ['message' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'Failed to submit exam questions: ' . $e->getMessage());
        }
    }

    public function show(ExamQuestion $exam_question)
    {
        $exam_question->load([
            'teacher', 'subject', 'classRoom', 'section', 'exam',
            'academicYear', 'term', 'branch', 'departmentHead', 'principal'
        ]);

        $user = auth()->user();

        $canReviewDepartment = $user->role === 'admin'
            || $user->hasRole('department_head');

        $canReviewPrincipal = $user->role === 'admin'
            || $user->hasRole('branch_principal');

        $canRequestRevision = $canReviewDepartment || $canReviewPrincipal;

        $canEdit = ($user->role === 'teacher' && $exam_question->isEditable()
                    && $user->teacherProfile && $exam_question->teacher_id === $user->teacherProfile->id)
                   || $user->role === 'admin';

        $canDelete = ($user->role === 'teacher' && $exam_question->isDeletable()
                      && $user->teacherProfile && $exam_question->teacher_id === $user->teacherProfile->id)
                     || $user->role === 'admin';

        return view('admin.exam-questions.show', compact(
            'exam_question', 'canReviewDepartment', 'canReviewPrincipal',
            'canRequestRevision', 'canEdit', 'canDelete'
        ));
    }

    public function edit(ExamQuestion $exam_question)
    {
        $user = auth()->user();

        // Only teacher who owns it (with editable status) or admin can edit
        if ($user->role === 'teacher') {
            $teacherProfile = $user->teacherProfile;
            if (!$teacherProfile || $exam_question->teacher_id !== $teacherProfile->id) {
                abort(403, 'You can only edit your own exam questions.');
            }
            if (!$exam_question->isEditable()) {
                abort(403, 'This exam question cannot be edited in its current status.');
            }
        } elseif ($user->role !== 'admin') {
            abort(403, 'You do not have permission to edit exam questions.');
        }

        $exam_question->load(['teacher', 'subject', 'classRoom', 'section', 'exam', 'academicYear', 'term', 'branch']);

        $subjects = Subject::orderBy('name')->get();
        $classes = Classroom::orderBy('numeric_name')->orderBy('name')->get();
        $exams = Exam::orderByDesc('start_date')->get();
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $terms = Term::orderBy('id')->get();
        $branches = Branch::orderBy('name')->get();
        $sections = $exam_question->classRoom ? $exam_question->classRoom->sections : collect();

        $teacherProfile = $user->teacherProfile;

        return view('admin.exam-questions.edit', compact(
            'exam_question', 'subjects', 'classes', 'exams', 'academicYears',
            'terms', 'branches', 'sections', 'teacherProfile'
        ));
    }

    public function update(Request $request, ExamQuestion $exam_question)
    {
        $user = auth()->user();

        // Access check
        if ($user->role === 'teacher') {
            $teacherProfile = $user->teacherProfile;
            if (!$teacherProfile || $exam_question->teacher_id !== $teacherProfile->id) {
                abort(403);
            }
            if (!$exam_question->isEditable()) {
                abort(403, 'This exam question cannot be edited in its current status.');
            }
        } elseif ($user->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'title'             => 'required|string|max:500',
            'subject_id'        => 'required|exists:subjects,id',
            'class_id'          => 'required|exists:classes,id',
            'section_id'        => 'nullable|exists:sections,id',
            'exam_id'           => 'nullable|exists:exams,id',
            'academic_year_id'  => 'nullable|exists:academic_years,id',
            'term_id'           => 'nullable|exists:terms,id',
            'branch_id'         => 'nullable|exists:branches,id',
            'description'       => 'nullable|string|max:5000',
            'questions'         => 'required|string',
            'question_type'     => 'required|in:multiple_choice,essay,short_answer,mixed',
            'total_marks'       => 'required|integer|min:1',
            'duration_minutes'  => 'nullable|integer|min:1',
        ]);

        // Resubmit: reset status to pending_department
        if (in_array($exam_question->status, ['revision', 'rejected_by_department', 'rejected_by_principal'])) {
            $validated['status'] = 'pending_department';
            $validated['department_head_comment'] = null;
            $validated['department_head_id'] = null;
            $validated['department_head_reviewed_at'] = null;
            $validated['principal_comment'] = null;
            $validated['principal_id'] = null;
            $validated['principal_reviewed_at'] = null;
        }

        // Handle file attachment replacement
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $validated['attachment'] = $file->storeAs('exam_questions', $filename, 'public');
        }

        try {
            $exam_question->update($validated);

            $message = in_array($exam_question->getOriginal('status'), ['revision', 'rejected_by_department', 'rejected_by_principal'])
                ? 'Exam questions resubmitted successfully. Awaiting department head review.'
                : 'Exam questions updated successfully.';

            return redirect()->route('admin.exam-questions.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('ExamQuestion update failed', ['message' => $e->getMessage()]);
            return back()->withInput()
                ->with('error', 'Failed to update exam questions: ' . $e->getMessage());
        }
    }

    public function destroy(ExamQuestion $exam_question)
    {
        $user = auth()->user();

        if ($user->role === 'teacher') {
            $teacherProfile = $user->teacherProfile;
            if (!$teacherProfile || $exam_question->teacher_id !== $teacherProfile->id) {
                abort(403);
            }
            if (!$exam_question->isDeletable()) {
                abort(403, 'Only pending exam questions can be deleted.');
            }
        } elseif ($user->role !== 'admin') {
            abort(403);
        }

        $exam_question->delete();

        return redirect()->route('admin.exam-questions.index')
            ->with('success', 'Exam questions deleted successfully.');
    }

    public function reviewByDepartment(Request $request, ExamQuestion $exam_question)
    {
        $user = auth()->user();

        if ($user->role !== 'admin' && !$user->hasRole('department_head')) {
            abort(403, 'Only department heads or admins can perform department reviews.');
        }

        if ($exam_question->status !== 'pending_department') {
            return back()->with('error', 'This question is not pending department review.');
        }

        $validated = $request->validate([
            'action'   => 'required|in:approve,reject',
            'comment'  => 'nullable|string|max:2000',
        ]);

        try {
            if ($validated['action'] === 'approve') {
                $exam_question->update([
                    'status'                     => 'pending_principal',
                    'department_head_id'         => $user->id,
                    'department_head_comment'    => $validated['comment'] ?? null,
                    'department_head_reviewed_at' => now(),
                ]);
                $message = 'Exam questions approved and forwarded to principal for review.';
            } else {
                $exam_question->update([
                    'status'                     => 'rejected_by_department',
                    'department_head_id'         => $user->id,
                    'department_head_comment'    => $validated['comment'] ?? 'Rejected by department head.',
                    'department_head_reviewed_at' => now(),
                ]);
                $message = 'Exam questions rejected.';
            }

            return redirect()->route('admin.exam-questions.show', $exam_question->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Department review failed', ['message' => $e->getMessage()]);
            return back()->with('error', 'Failed to process review: ' . $e->getMessage());
        }
    }

    public function reviewByPrincipal(Request $request, ExamQuestion $exam_question)
    {
        $user = auth()->user();

        if ($user->role !== 'admin' && !$user->hasRole('branch_principal')) {
            abort(403, 'Only branch principals or admins can perform principal reviews.');
        }

        if ($exam_question->status !== 'pending_principal') {
            return back()->with('error', 'This question is not pending principal review.');
        }

        $validated = $request->validate([
            'action'   => 'required|in:approve,reject',
            'comment'  => 'nullable|string|max:2000',
        ]);

        try {
            if ($validated['action'] === 'approve') {
                $exam_question->update([
                    'status'                => 'approved',
                    'principal_id'          => $user->id,
                    'principal_comment'     => $validated['comment'] ?? null,
                    'principal_reviewed_at' => now(),
                ]);
                $message = 'Exam questions fully approved!';
            } else {
                $exam_question->update([
                    'status'                => 'rejected_by_principal',
                    'principal_id'          => $user->id,
                    'principal_comment'     => $validated['comment'] ?? 'Rejected by principal.',
                    'principal_reviewed_at' => now(),
                ]);
                $message = 'Exam questions rejected by principal.';
            }

            return redirect()->route('admin.exam-questions.show', $exam_question->id)
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Principal review failed', ['message' => $e->getMessage()]);
            return back()->with('error', 'Failed to process review: ' . $e->getMessage());
        }
    }

    /**
     * API: Get sections for a given class (for dependent dropdowns)
     */
    public function apiSectionsByClass(Request $request)
    {
        $classId = $request->query('class_id');
        if (!$classId) {
            return response()->json([]);
        }

        return response()->json(
            Section::where('class_id', $classId)->orderBy('name')->get(['id', 'name'])
        );
    }

    /**
     * API: Get exam details (academic_year_id, term_id) for auto-fill
     */
    public function apiExamDetails(Exam $exam)
    {
        return response()->json([
            'academic_year_id' => $exam->academic_year_id,
            'term_id'          => $exam->term_id,
        ]);
    }

    public function requestRevision(Request $request, ExamQuestion $exam_question)
    {
        $user = auth()->user();

        if ($user->role !== 'admin' && !$user->hasRole('branch_principal') && !$user->hasRole('department_head')) {
            abort(403, 'You do not have permission to request revisions.');
        }

        if (!in_array($exam_question->status, ['pending_department', 'pending_principal'])) {
            return back()->with('error', 'Revision can only be requested for pending questions.');
        }

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        try {
            $updateData = [
                'status'   => 'revision',
            ];

            // Track who requested the revision
            if ($exam_question->status === 'pending_department') {
                $updateData['department_head_id'] = $user->id;
                $updateData['department_head_comment'] = $validated['comment'];
                $updateData['department_head_reviewed_at'] = now();
            } elseif ($exam_question->status === 'pending_principal') {
                $updateData['principal_id'] = $user->id;
                $updateData['principal_comment'] = $validated['comment'];
                $updateData['principal_reviewed_at'] = now();
            }

            $exam_question->update($updateData);

            return redirect()->route('admin.exam-questions.show', $exam_question->id)
                ->with('success', 'Revision requested. The teacher will be notified to update their questions.');
        } catch (\Exception $e) {
            Log::error('Request revision failed', ['message' => $e->getMessage()]);
            return back()->with('error', 'Failed to request revision: ' . $e->getMessage());
        }
    }
}
