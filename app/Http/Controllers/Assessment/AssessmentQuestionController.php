<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentOption;
use App\Models\AssessmentAnswer;
use App\Models\Subject;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\TeacherAssignment;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssessmentQuestionController extends Controller
{
    // ── Teacher: List Questions ─────────────────────────────

    public function index(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        $query = AssessmentQuestion::with(['subject', 'classroom', 'section', 'options', 'answers']);

        // Non-admin: only see own questions
        if (!in_array($user->role, ['admin', 'super_admin', 'branch_principal', 'general_manager'])) {
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            }
        }

        // Filters
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }
        if ($request->filled('question_type')) {
            $query->where('question_type', $request->question_type);
        }
        if ($request->filled('topic')) {
            $query->where('topic', 'like', "%{$request->topic}%");
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('question_text', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('topic', 'like', "%{$search}%");
            });
        }

        $questions = $query->latest()->paginate(20);

        // Get subjects the teacher is assigned to
        $activeAy = AcademicYear::where('is_current', true)->first();
        $subjects = $this->getTeacherSubjects($teacher, $activeAy);
        $classes = $this->getTeacherClasses($teacher, $activeAy);
        $topics = AssessmentQuestion::when($teacher, fn($q) => $q->where('teacher_id', $teacher->id))
            ->whereNotNull('topic')->distinct()->orderBy('topic')->pluck('topic');

        return view('admin.assessment-questions.index', compact(
            'questions', 'subjects', 'classes', 'topics', 'teacher', 'activeAy'
        ));
    }

    // ── Teacher: Create Question ────────────────────────────

    public function create()
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();
        $activeAy = AcademicYear::where('is_current', true)->first();

        $subjects = $this->getTeacherSubjects($teacher, $activeAy);
        $classes = $this->getTeacherClasses($teacher, $activeAy);

        return view('admin.assessment-questions.create', compact('subjects', 'classes', 'teacher', 'activeAy'));
    }

    // ── Teacher: Store Question ─────────────────────────────

    public function store(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        if (!$teacher && !in_array($user->role, ['admin', 'super_admin'])) {
            return back()->with('error', 'Teacher profile not found.')->withInput();
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'question_text' => 'required|string|max:5000',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'topic' => 'nullable|string|max:255',
            'marks' => 'integer|min:1|max:100',
            'hint' => 'nullable|string|max:2000',
            'explanation' => 'nullable|string|max:10000',
            'worked_out_solution' => 'nullable|string|max:20000',
            'is_active' => 'boolean',
            // Options for multiple choice
            'options' => 'required_if:question_type,multiple_choice|array|min:2|max:6',
            'options.*.option_text' => 'required_with:options|string|max:1000',
            'options.*.option_label' => 'required_with:options|string|max:1',
            'options.*.is_correct' => 'boolean',
            // True/False
            'correct_tf' => 'required_if:question_type,true_false|in:true,false',
        ]);

        $activeAy = AcademicYear::where('is_current', true)->first();

        $question = AssessmentQuestion::create([
            'teacher_id' => $teacher?->id,
            'subject_id' => $validated['subject_id'],
            'class_id' => $validated['class_id'],
            'section_id' => $validated['section_id'] ?? null,
            'academic_year_id' => $activeAy?->id,
            'branch_id' => $teacher?->user?->branch_id ?? $user->branch_id,
            'title' => $validated['title'] ?? null,
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'hint' => $validated['hint'] ?? null,
            'explanation' => $validated['explanation'] ?? null,
            'worked_out_solution' => $validated['worked_out_solution'] ?? null,
            'difficulty' => $validated['difficulty'],
            'topic' => $validated['topic'] ?? null,
            'marks' => $validated['marks'] ?? 1,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Create options based on question type
        if ($validated['question_type'] === 'multiple_choice' && !empty($validated['options'])) {
            foreach ($validated['options'] as $idx => $opt) {
                AssessmentOption::create([
                    'assessment_question_id' => $question->id,
                    'option_text' => $opt['option_text'],
                    'option_label' => $opt['option_label'] ?? chr(65 + $idx), // A, B, C...
                    'is_correct' => isset($opt['is_correct']) && $opt['is_correct'],
                    'sort_order' => $idx,
                ]);
            }
        } elseif ($validated['question_type'] === 'true_false') {
            AssessmentOption::create([
                'assessment_question_id' => $question->id,
                'option_text' => 'True',
                'option_label' => 'A',
                'is_correct' => $validated['correct_tf'] === 'true',
                'sort_order' => 0,
            ]);
            AssessmentOption::create([
                'assessment_question_id' => $question->id,
                'option_text' => 'False',
                'option_label' => 'B',
                'is_correct' => $validated['correct_tf'] === 'false',
                'sort_order' => 1,
            ]);
        }

        return redirect()->route('admin.assessment-questions.index')
            ->with('success', 'Question created successfully.');
    }

    // ── Show single question ────────────────────────────────

    public function show(AssessmentQuestion $assessment_question)
    {
        $assessment_question->load(['subject', 'classroom', 'section', 'options', 'teacher', 'answers.student']);

        $answerStats = $assessment_question->getStudentAnswerStats();

        // Option distribution
        $optionDistribution = [];
        if ($assessment_question->question_type !== 'short_answer') {
            foreach ($assessment_question->options as $option) {
                $optionDistribution[$option->option_label] = [
                    'text' => $option->option_text,
                    'count' => AssessmentAnswer::where('assessment_question_id', $assessment_question->id)
                        ->where('assessment_option_id', $option->id)
                        ->count(),
                    'is_correct' => $option->is_correct,
                ];
            }
        }

        return view('admin.assessment-questions.show', [
            'question' => $assessment_question,
            'answerStats' => $answerStats,
            'optionDistribution' => $optionDistribution,
        ]);
    }

    // ── Edit Question ───────────────────────────────────────

    public function edit(AssessmentQuestion $assessment_question)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        // Only creator or admin can edit
        if (!in_array($user->role, ['admin', 'super_admin']) && $assessment_question->teacher_id !== $teacher?->id) {
            abort(403, 'You can only edit your own questions.');
        }

        $assessment_question->load('options');
        $activeAy = AcademicYear::where('is_current', true)->first();
        $subjects = $this->getTeacherSubjects($teacher, $activeAy);
        $classes = $this->getTeacherClasses($teacher, $activeAy);

        return view('admin.assessment-questions.edit', compact(
            'assessment_question', 'subjects', 'classes', 'teacher', 'activeAy'
        ));
    }

    // ── Update Question ─────────────────────────────────────

    public function update(Request $request, AssessmentQuestion $assessment_question)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        if (!in_array($user->role, ['admin', 'super_admin']) && $assessment_question->teacher_id !== $teacher?->id) {
            abort(403, 'You can only edit your own questions.');
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'question_text' => 'required|string|max:5000',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer',
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'topic' => 'nullable|string|max:255',
            'marks' => 'integer|min:1|max:100',
            'hint' => 'nullable|string|max:2000',
            'explanation' => 'nullable|string|max:10000',
            'worked_out_solution' => 'nullable|string|max:20000',
            'is_active' => 'boolean',
            'options' => 'required_if:question_type,multiple_choice|array|min:2|max:6',
            'options.*.id' => 'nullable|exists:assessment_options,id',
            'options.*.option_text' => 'required_with:options|string|max:1000',
            'options.*.option_label' => 'required_with:options|string|max:1',
            'options.*.is_correct' => 'boolean',
            'correct_tf' => 'required_if:question_type,true_false|in:true,false',
        ]);

        $assessment_question->update([
            'title' => $validated['title'] ?? null,
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'subject_id' => $validated['subject_id'],
            'class_id' => $validated['class_id'],
            'section_id' => $validated['section_id'] ?? null,
            'difficulty' => $validated['difficulty'],
            'topic' => $validated['topic'] ?? null,
            'marks' => $validated['marks'] ?? 1,
            'hint' => $validated['hint'] ?? null,
            'explanation' => $validated['explanation'] ?? null,
            'worked_out_solution' => $validated['worked_out_solution'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Sync options
        // Delete old options and recreate
        $assessment_question->options()->delete();

        if ($validated['question_type'] === 'multiple_choice' && !empty($validated['options'])) {
            foreach ($validated['options'] as $idx => $opt) {
                if (empty($opt['option_text'])) continue;
                AssessmentOption::create([
                    'assessment_question_id' => $assessment_question->id,
                    'option_text' => $opt['option_text'],
                    'option_label' => $opt['option_label'] ?? chr(65 + $idx),
                    'is_correct' => isset($opt['is_correct']) && $opt['is_correct'],
                    'sort_order' => $idx,
                ]);
            }
        } elseif ($validated['question_type'] === 'true_false') {
            AssessmentOption::create([
                'assessment_question_id' => $assessment_question->id,
                'option_text' => 'True',
                'option_label' => 'A',
                'is_correct' => $validated['correct_tf'] === 'true',
                'sort_order' => 0,
            ]);
            AssessmentOption::create([
                'assessment_question_id' => $assessment_question->id,
                'option_text' => 'False',
                'option_label' => 'B',
                'is_correct' => $validated['correct_tf'] === 'false',
                'sort_order' => 1,
            ]);
        }

        return redirect()->route('admin.assessment-questions.index')
            ->with('success', 'Question updated successfully.');
    }

    // ── Delete Question ─────────────────────────────────────

    public function destroy(AssessmentQuestion $assessment_question)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        if (!in_array($user->role, ['admin', 'super_admin']) && $assessment_question->teacher_id !== $teacher?->id) {
            abort(403, 'You can only delete your own questions.');
        }

        $assessment_question->delete();

        return back()->with('success', 'Question deleted successfully.');
    }

    // ── Toggle Active ───────────────────────────────────────

    public function toggleActive(AssessmentQuestion $assessment_question)
    {
        $assessment_question->update(['is_active' => !$assessment_question->is_active]);
        return back()->with('success', 'Question status updated.');
    }

    // ── Bulk Create (Quick Add) ────────────────────────────

    public function bulkCreate()
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();
        $activeAy = AcademicYear::where('is_current', true)->first();
        $subjects = $this->getTeacherSubjects($teacher, $activeAy);
        $classes = $this->getTeacherClasses($teacher, $activeAy);

        return view('admin.assessment-questions.bulk-create', compact('subjects', 'classes', 'teacher', 'activeAy'));
    }

    public function bulkStore(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'difficulty' => 'required|in:easy,medium,hard',
            'topic' => 'nullable|string|max:255',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string|max:5000',
            'questions.*.question_type' => 'required|in:multiple_choice,true_false,short_answer',
            'questions.*.hint' => 'nullable|string|max:2000',
            'questions.*.explanation' => 'nullable|string|max:10000',
            'questions.*.worked_out_solution' => 'nullable|string|max:20000',
            'questions.*.marks' => 'nullable|integer|min:1|max:100',
        ]);

        $activeAy = AcademicYear::where('is_current', true)->first();
        $count = 0;

        foreach ($validated['questions'] as $qData) {
            $question = AssessmentQuestion::create([
                'teacher_id' => $teacher?->id,
                'subject_id' => $validated['subject_id'],
                'class_id' => $validated['class_id'],
                'section_id' => $validated['section_id'] ?? null,
                'academic_year_id' => $activeAy?->id,
                'branch_id' => $teacher?->user?->branch_id ?? $user->branch_id,
                'question_text' => $qData['question_text'],
                'question_type' => $qData['question_type'],
                'hint' => $qData['hint'] ?? null,
                'explanation' => $qData['explanation'] ?? null,
                'worked_out_solution' => $qData['worked_out_solution'] ?? null,
                'difficulty' => $validated['difficulty'],
                'topic' => $validated['topic'] ?? null,
                'marks' => $qData['marks'] ?? 1,
                'is_active' => true,
            ]);
            $count++;
        }

        return redirect()->route('admin.assessment-questions.index')
            ->with('success', "{$count} questions created successfully.");
    }

    // ── API: Get sections for a class ──────────────────────

    public function apiSections($classId)
    {
        $sections = Section::where('class_id', $classId)->orderBy('name')->get(['id', 'name']);
        return response()->json($sections);
    }

    // ── Report: Student performance ─────────────────────────

    public function report(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();
        $activeAy = AcademicYear::where('is_current', true)->first();

        $subjects = $this->getTeacherSubjects($teacher, $activeAy);
        $classes = $this->getTeacherClasses($teacher, $activeAy);

        $query = AssessmentAnswer::with(['student.classroom', 'student.section', 'question.subject']);

        // Filter by teacher's questions
        if ($teacher && !in_array($user->role, ['admin', 'super_admin'])) {
            $query->whereHas('question', fn($q) => $q->where('teacher_id', $teacher->id));
        }

        if ($request->filled('subject_id')) {
            $query->whereHas('question', fn($q) => $q->where('subject_id', $request->subject_id));
        }
        if ($request->filled('class_id')) {
            $query->whereHas('question', fn($q) => $q->where('class_id', $request->class_id));
        }

        $answers = $query->latest()->paginate(30);

        // Summary stats
        $totalQuestions = AssessmentQuestion::when($teacher, fn($q) => $q->where('teacher_id', $teacher?->id))
            ->when($request->subject_id, fn($q) => $q->where('subject_id', $request->subject_id))
            ->count();

        $totalAnswers = $query->count();
        $correctAnswers = (clone $query)->where('is_correct', true)->count();

        return view('admin.assessment-questions.report', compact(
            'answers', 'subjects', 'classes', 'totalQuestions', 'totalAnswers', 'correctAnswers'
        ));
    }

    // ── Helpers ─────────────────────────────────────────────

    private function getTeacherSubjects($teacher, $activeAy)
    {
        if (!$teacher || !$activeAy) {
            return Subject::orderBy('name')->get();
        }

        $subjectIds = TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('academic_year_id', $activeAy->id)
            ->pluck('subject_id')
            ->unique();

        return Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
    }

    private function getTeacherClasses($teacher, $activeAy)
    {
        if (!$teacher || !$activeAy) {
            return ClassRoom::orderBy('name')->get();
        }

        $classIds = TeacherAssignment::where('teacher_id', $teacher->id)
            ->where('academic_year_id', $activeAy->id)
            ->pluck('class_id')
            ->unique();

        return ClassRoom::whereIn('id', $classIds)->orderBy('name')->get();
    }
}
