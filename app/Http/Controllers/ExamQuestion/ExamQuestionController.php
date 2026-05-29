<?php

namespace App\Http\Controllers\ExamQuestion;

use App\Http\Controllers\Controller;
use App\Models\ExamQuestion;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\ClassRoom;
use App\Models\Term;
use App\Models\AcademicYear;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExamQuestionController extends Controller
{
    // ── Teacher: List own questions ────────────────────────
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = ExamQuestion::with(['teacher', 'subject', 'classRoom', 'term', 'academicYear', 'deptReviewer', 'principalReviewer']);

        if ($user->role === 'teacher') {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher) {
                $teacher = Teacher::where('email', $user->email)->first();
            }
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            } else {
                $query->whereRaw('1 = 0'); // No teacher profile = no questions
            }
        } elseif ($user->role === 'department_head') {
            // Department head sees questions from teachers in their department
            $dept = Department::where('head_user_id', $user->id)->first();
            if ($dept) {
                $teacherIds = $dept->teachers()->pluck('teachers.id');
                $query->whereIn('teacher_id', $teacherIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->role === 'branch_principal') {
            // Principal sees dept-approved questions pending their review + all others
            $statusFilter = $request->get('status');
            if ($statusFilter) {
                $query->where('status', $statusFilter);
            }
        }
        // admin/super_admin/general_manager see all

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Subject filter
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }

        $data = $query->latest()->paginate(15)->withQueryString();
        $subjects = Subject::orderBy('name')->get();
        $statuses = [
            'draft' => 'Draft',
            'submitted' => 'Pending Dept. Review',
            'dept_approved' => 'Pending Principal',
            'dept_rejected' => 'Rejected by Dept.',
            'principal_approved' => 'Approved',
            'principal_rejected' => 'Rejected by Principal',
        ];

        return view('admin.ExamQuestion.index', compact('data', 'subjects', 'statuses'));
    }

    // ── Teacher: Create form ───────────────────────────────
    public function create()
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) {
            $teacher = Teacher::where('email', $user->email)->first();
        }

        $subjects = Subject::orderBy('name')->get();
        $classes = ClassRoom::orderBy('numeric_name')->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $allTerms = Term::orderBy('id')->get();

        return view('admin.ExamQuestion.create', compact('subjects', 'classes', 'academicYears', 'allTerms', 'teacher'));
    }

    // ── Teacher: Store new question ────────────────────────
    public function store(Request $request)
    {
        $user = Auth::user();
        $teacher = Teacher::where('user_id', $user->id)->first();
        if (!$teacher) {
            $teacher = Teacher::where('email', $user->email)->first();
        }

        if (!$teacher) {
            return back()->with('error', 'No teacher profile found for your account.')->withInput();
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'nullable|exists:classes,id',
            'term_id' => 'nullable|exists:terms,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer,essay,fill_blank,mixed',
            'content' => 'nullable|string',
            'total_marks' => 'required|integer|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xlsx,ppt,pptx,txt,jpg,jpeg,png|max:10240',
            'action' => 'nullable|in:draft,submit',
        ]);

        $validated['teacher_id'] = $teacher->id;
        $action = $validated['action'] ?? 'draft';
        unset($validated['action']);

        if ($action === 'submit') {
            $validated['status'] = 'submitted';
            $validated['submitted_at'] = now();
        } else {
            $validated['status'] = 'draft';
        }

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('exam_questions', $filename, 'public');
            $validated['attachment'] = $path;
        }

        ExamQuestion::create($validated);

        $message = $action === 'submit'
            ? 'Exam question submitted for department head review.'
            : 'Exam question saved as draft.';

        return redirect()->route('admin.exam-questions.index')->with('success', $message);
    }

    // ── View single question ───────────────────────────────
    public function show(ExamQuestion $examQuestion)
    {
        $examQuestion->load(['teacher', 'subject', 'classRoom', 'term', 'academicYear', 'deptReviewer', 'principalReviewer']);
        return view('admin.ExamQuestion.show', compact('examQuestion'));
    }

    // ── Teacher: Edit form ─────────────────────────────────
    public function edit(ExamQuestion $examQuestion)
    {
        if (!$examQuestion->canBeEdited()) {
            return back()->with('error', 'This question cannot be edited in its current status.');
        }

        $subjects = Subject::orderBy('name')->get();
        $classes = ClassRoom::orderBy('numeric_name')->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $allTerms = Term::orderBy('id')->get();

        return view('admin.ExamQuestion.edit', compact('examQuestion', 'subjects', 'classes', 'academicYears', 'allTerms'));
    }

    // ── Teacher: Update question ───────────────────────────
    public function update(Request $request, ExamQuestion $examQuestion)
    {
        if (!$examQuestion->canBeEdited()) {
            return back()->with('error', 'This question cannot be edited in its current status.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'nullable|exists:classes,id',
            'term_id' => 'nullable|exists:terms,id',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer,essay,fill_blank,mixed',
            'content' => 'nullable|string',
            'total_marks' => 'required|integer|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xlsx,ppt,pptx,txt,jpg,jpeg,png|max:10240',
            'action' => 'nullable|in:draft,submit',
        ]);

        $action = $validated['action'] ?? 'draft';
        unset($validated['action']);

        if ($action === 'submit') {
            $validated['status'] = 'submitted';
            $validated['submitted_at'] = now();
            // Clear previous rejection data when resubmitting
            $validated['dept_reviewed_by'] = null;
            $validated['dept_reviewed_at'] = null;
            $validated['dept_comments'] = null;
            $validated['principal_reviewed_by'] = null;
            $validated['principal_reviewed_at'] = null;
            $validated['principal_comments'] = null;
        }

        // Handle file upload
        if ($request->hasFile('attachment')) {
            // Delete old file
            if ($examQuestion->attachment && Storage::disk('public')->exists($examQuestion->attachment)) {
                Storage::disk('public')->delete($examQuestion->attachment);
            }
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('exam_questions', $filename, 'public');
            $validated['attachment'] = $path;
        }

        $examQuestion->update($validated);

        $message = $action === 'submit'
            ? 'Exam question submitted for department head review.'
            : 'Exam question updated.';

        return redirect()->route('admin.exam-questions.index')->with('success', $message);
    }

    // ── Delete question ────────────────────────────────────
    public function destroy(ExamQuestion $examQuestion)
    {
        if ($examQuestion->attachment && Storage::disk('public')->exists($examQuestion->attachment)) {
            Storage::disk('public')->delete($examQuestion->attachment);
        }
        $examQuestion->delete();
        return redirect()->route('admin.exam-questions.index')->with('success', 'Exam question deleted.');
    }

    // ── Teacher: Submit for review ─────────────────────────
    public function submit(ExamQuestion $examQuestion)
    {
        if (!$examQuestion->canBeSubmitted()) {
            return back()->with('error', 'This question cannot be submitted in its current status.');
        }

        $examQuestion->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'dept_reviewed_by' => null,
            'dept_reviewed_at' => null,
            'dept_comments' => null,
            'principal_reviewed_by' => null,
            'principal_reviewed_at' => null,
            'principal_comments' => null,
        ]);

        return redirect()->route('admin.exam-questions.index')->with('success', 'Exam question submitted for department head review.');
    }

    // ── Department Head: Review (approve/reject) ───────────
    public function deptReview(Request $request, ExamQuestion $examQuestion)
    {
        if (!$examQuestion->canBeReviewedByDept()) {
            return back()->with('error', 'This question is not pending department review.');
        }

        $validated = $request->validate([
            'dept_comments' => 'nullable|string',
            'action' => 'required|in:approve,reject',
        ]);

        if ($validated['action'] === 'approve') {
            $examQuestion->update([
                'status' => 'dept_approved',
                'dept_reviewed_by' => Auth::id(),
                'dept_reviewed_at' => now(),
                'dept_comments' => $validated['dept_comments'],
            ]);
            return redirect()->route('admin.exam-questions.index')->with('success', 'Exam question approved and forwarded to principal.');
        } else {
            $examQuestion->update([
                'status' => 'dept_rejected',
                'dept_reviewed_by' => Auth::id(),
                'dept_reviewed_at' => now(),
                'dept_comments' => $validated['dept_comments'],
            ]);
            return redirect()->route('admin.exam-questions.index')->with('success', 'Exam question rejected.');
        }
    }

    // ── Principal: Review (approve/reject) ─────────────────
    public function principalReview(Request $request, ExamQuestion $examQuestion)
    {
        if (!$examQuestion->canBeReviewedByPrincipal()) {
            return back()->with('error', 'This question is not pending principal review.');
        }

        $validated = $request->validate([
            'principal_comments' => 'nullable|string',
            'action' => 'required|in:approve,reject',
        ]);

        if ($validated['action'] === 'approve') {
            $examQuestion->update([
                'status' => 'principal_approved',
                'principal_reviewed_by' => Auth::id(),
                'principal_reviewed_at' => now(),
                'principal_comments' => $validated['principal_comments'],
            ]);
            return redirect()->route('admin.exam-questions.index')->with('success', 'Exam question approved by principal.');
        } else {
            $examQuestion->update([
                'status' => 'principal_rejected',
                'principal_reviewed_by' => Auth::id(),
                'principal_reviewed_at' => now(),
                'principal_comments' => $validated['principal_comments'],
            ]);
            return redirect()->route('admin.exam-questions.index')->with('success', 'Exam question rejected by principal.');
        }
    }
}
