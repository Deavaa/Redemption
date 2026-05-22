<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentOption;
use App\Models\Student;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentAssessmentController extends Controller
{
    // ── Get authenticated student ───────────────────────────

    private function getStudent()
    {
        $user = Auth::user();
        $student = Student::where('user_id', $user->id)->first();
        if (!$student) {
            $student = Student::where('email', $user->email)->first();
        }
        return $student;
    }

    // ── Dashboard: Overview of available subjects & stats ────

    public function index()
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('student.dashboard')->with('error', 'Student profile not found.');
        }

        $activeAy = AcademicYear::where('is_active', true)->first();

        // Get subjects assigned to student's class+section
        $subjectIds = TeacherAssignment::where('class_id', $student->class_id)
            ->where('academic_year_id', $activeAy?->id)
            ->when($student->section_id, fn($q) => $q->where(function ($query) use ($student) {
                $query->where('section_id', $student->section_id)
                      ->orWhereNull('section_id');
            }))
            ->pluck('subject_id')
            ->unique();

        $subjects = Subject::whereIn('id', $subjectIds)->orderBy('name')->get();

        // Question counts per subject
        $subjectStats = [];
        foreach ($subjects as $subject) {
            $totalQuestions = AssessmentQuestion::active()
                ->where('subject_id', $subject->id)
                ->where('class_id', $student->class_id)
                ->where(function ($q) use ($student) {
                    $q->whereNull('section_id')
                      ->orWhere('section_id', $student->section_id);
                })
                ->count();

            $answeredQuestions = AssessmentAnswer::where('student_id', $student->id)
                ->whereHas('question', fn($q) => $q->where('subject_id', $subject->id)->where('class_id', $student->class_id))
                ->distinct('assessment_question_id')
                ->count('assessment_question_id');

            $correctAnswers = AssessmentAnswer::where('student_id', $student->id)
                ->where('is_correct', true)
                ->whereHas('question', fn($q) => $q->where('subject_id', $subject->id)->where('class_id', $student->class_id))
                ->count();

            $subjectStats[$subject->id] = [
                'total' => $totalQuestions,
                'answered' => $answeredQuestions,
                'correct' => $correctAnswers,
                'remaining' => max(0, $totalQuestions - $answeredQuestions),
                'accuracy' => $answeredQuestions > 0 ? round(($correctAnswers / $answeredQuestions) * 100, 1) : 0,
            ];
        }

        // Overall stats
        $overallStats = AssessmentAnswer::getStudentStats($student->id);

        // Recent attempts
        $recentAttempts = AssessmentAnswer::where('student_id', $student->id)
            ->with(['question.subject', 'option'])
            ->latest()
            ->take(10)
            ->get();

        return view('student.assessment.index', compact(
            'student', 'subjects', 'subjectStats', 'overallStats', 'recentAttempts'
        ));
    }

    // ── List questions for a specific subject ────────────────

    public function subjectQuestions(Request $request, $subjectId)
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('student.dashboard')->with('error', 'Student profile not found.');
        }

        $subject = Subject::findOrFail($subjectId);

        $query = AssessmentQuestion::active()
            ->with(['options', 'teacher'])
            ->where('subject_id', $subjectId)
            ->where('class_id', $student->class_id)
            ->where(function ($q) use ($student) {
                $q->whereNull('section_id')
                  ->orWhere('section_id', $student->section_id);
            });

        // Filter by difficulty
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Filter by topic
        if ($request->filled('topic')) {
            $query->where('topic', $request->topic);
        }

        // Filter by status (answered/unanswered)
        $answeredIds = AssessmentAnswer::where('student_id', $student->id)
            ->distinct()->pluck('assessment_question_id');

        if ($request->status === 'unanswered') {
            $query->whereNotIn('id', $answeredIds);
        } elseif ($request->status === 'answered') {
            $query->whereIn('id', $answeredIds);
        }

        $questions = $query->latest()->paginate(15);

        // Get topics for filter
        $topics = AssessmentQuestion::active()
            ->where('subject_id', $subjectId)
            ->where('class_id', $student->class_id)
            ->whereNotNull('topic')
            ->distinct()
            ->orderBy('topic')
            ->pluck('topic');

        // Get student's previous answers for these questions
        $previousAnswers = AssessmentAnswer::where('student_id', $student->id)
            ->whereIn('assessment_question_id', $questions->pluck('id'))
            ->get()
            ->keyBy('assessment_question_id');

        return view('student.assessment.subject', compact(
            'student', 'subject', 'questions', 'topics', 'previousAnswers', 'answeredIds'
        ));
    }

    // ── Show a single question for answering ─────────────────

    public function showQuestion($questionId)
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('student.dashboard')->with('error', 'Student profile not found.');
        }

        $question = AssessmentQuestion::active()
            ->with(['options', 'subject', 'classroom'])
            ->where('class_id', $student->class_id)
            ->findOrFail($questionId);

        // Check if student already answered
        $previousAnswer = AssessmentAnswer::where('student_id', $student->id)
            ->where('assessment_question_id', $questionId)
            ->latest()
            ->first();

        return view('student.assessment.question', compact(
            'student', 'question', 'previousAnswer'
        ));
    }

    // ── Submit Answer ────────────────────────────────────────

    public function submitAnswer(Request $request, $questionId)
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('student.dashboard')->with('error', 'Student profile not found.');
        }

        $question = AssessmentQuestion::active()->findOrFail($questionId);

        $rules = [];
        if ($question->question_type === 'multiple_choice' || $question->question_type === 'true_false') {
            $rules['option_id'] = 'required|exists:assessment_options,id';
        } else {
            $rules['student_answer'] = 'required|string|max:5000';
        }

        $validated = $request->validate($rules);

        // Determine if correct
        $isCorrect = false;
        $optionId = null;

        if ($question->question_type === 'multiple_choice' || $question->question_type === 'true_false') {
            $selectedOption = AssessmentOption::findOrFail($validated['option_id']);
            $isCorrect = $selectedOption->is_correct;
            $optionId = $selectedOption->id;
        }

        // Get next attempt number
        $lastAttempt = AssessmentAnswer::where('student_id', $student->id)
            ->where('assessment_question_id', $questionId)
            ->max('attempt_number');

        AssessmentAnswer::create([
            'student_id' => $student->id,
            'assessment_question_id' => $questionId,
            'assessment_option_id' => $optionId,
            'student_answer' => $validated['student_answer'] ?? null,
            'is_correct' => $isCorrect,
            'attempt_number' => ($lastAttempt ?? 0) + 1,
            'time_spent_seconds' => $request->input('time_spent'),
            'answered_at' => now(),
        ]);

        // Load question with full explanation data for the result page
        $question->load('options');

        // Get the correct option for display
        $correctOption = $question->getCorrectOption();

        return view('student.assessment.result', [
            'student' => $student,
            'question' => $question,
            'isCorrect' => $isCorrect,
            'selectedOptionId' => $optionId,
            'correctOption' => $correctOption,
            'studentAnswer' => $validated['student_answer'] ?? null,
        ]);
    }

    // ── Retake a question ───────────────────────────────────

    public function retakeQuestion($questionId)
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('student.dashboard')->with('error', 'Student profile not found.');
        }

        $question = AssessmentQuestion::active()
            ->with(['options', 'subject', 'classroom'])
            ->where('class_id', $student->class_id)
            ->findOrFail($questionId);

        return view('student.assessment.question', [
            'student' => $student,
            'question' => $question,
            'previousAnswer' => null, // Clear previous answer for retake
        ]);
    }

    // ── Progress / Performance Report ────────────────────────

    public function progress()
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('student.dashboard')->with('error', 'Student profile not found.');
        }

        $overallStats = AssessmentAnswer::getStudentStats($student->id);

        // Per-subject stats
        $activeAy = AcademicYear::where('is_active', true)->first();
        $subjectIds = TeacherAssignment::where('class_id', $student->class_id)
            ->where('academic_year_id', $activeAy?->id)
            ->pluck('subject_id')
            ->unique();

        $subjectProgress = [];
        foreach ($subjectIds as $subjectId) {
            $subject = Subject::find($subjectId);
            if (!$subject) continue;

            $stats = AssessmentAnswer::getStudentSubjectStats($student->id, $subjectId);

            // Difficulty breakdown
            $difficultyBreakdown = [];
            foreach (['easy', 'medium', 'hard'] as $diff) {
                $total = AssessmentAnswer::where('student_id', $student->id)
                    ->whereHas('question', fn($q) => $q->where('subject_id', $subjectId)->where('difficulty', $diff))
                    ->count();
                $correct = AssessmentAnswer::where('student_id', $student->id)
                    ->where('is_correct', true)
                    ->whereHas('question', fn($q) => $q->where('subject_id', $subjectId)->where('difficulty', $diff))
                    ->count();
                $difficultyBreakdown[$diff] = [
                    'total' => $total,
                    'correct' => $correct,
                    'rate' => $total > 0 ? round(($correct / $total) * 100, 1) : 0,
                ];
            }

            $subjectProgress[] = [
                'subject' => $subject,
                'stats' => $stats,
                'difficulty' => $difficultyBreakdown,
            ];
        }

        // Recent activity
        $recentActivity = AssessmentAnswer::where('student_id', $student->id)
            ->with(['question.subject'])
            ->latest()
            ->take(20)
            ->get();

        return view('student.assessment.progress', compact(
            'student', 'overallStats', 'subjectProgress', 'recentActivity'
        ));
    }
}
