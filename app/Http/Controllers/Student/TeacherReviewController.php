<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\TeacherAssignment;
use App\Models\TeacherReview;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherReviewController extends Controller
{
    /**
     * Get the currently logged-in student.
     */
    private function getStudent()
    {
        $user = Auth::user();
        $student = \App\Models\Student::where('user_id', $user->id)->first();
        if (!$student) {
            $student = \App\Models\Student::where('email', $user->email)->first();
        }
        return $student;
    }

    /**
     * List all reviews submitted by this student, and show pending teachers to review.
     */
    public function index()
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('student.dashboard')->with('error', 'Student profile not found.');
        }

        $academicYear = AcademicYear::where('is_current', true)->first();
        $activeTerm = $academicYear
            ? Term::where('academic_year_id', $academicYear->id)->where('is_active', true)->first()
            : null;

        // Get all terms for the current academic year
        $terms = $academicYear
            ? Term::where('academic_year_id', $academicYear->id)->orderBy('term_number')->get()
            : collect();

        $selectedTermId = request('term_id', $activeTerm?->id);
        $selectedTerm = $selectedTermId ? Term::find($selectedTermId) : $activeTerm;

        // Get teachers assigned to this student's class
        $assignedTeachers = collect();
        if ($student->class_id && $selectedTerm) {
            $assignedTeachers = TeacherAssignment::with(['teacher', 'subject'])
                ->where('class_id', $student->class_id)
                ->when($student->section_id, fn($q) => $q->where(function ($query) use ($student) {
                    $query->where('section_id', $student->section_id)
                          ->orWhereNull('section_id');
                }))
                ->where('academic_year_id', $academicYear->id)
                ->get()
                ->unique('teacher_id')
                ->map(fn($a) => $a->teacher);
        }

        // Get already submitted reviews for this term
        $submittedReviews = TeacherReview::with('teacher')
            ->where('student_id', $student->id)
            ->where('term_id', $selectedTermId)
            ->get()
            ->keyBy('teacher_id');

        // Get all reviews by this student (for history)
        $allReviews = TeacherReview::with(['teacher', 'term', 'academicYear'])
            ->where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('student.teacher-review.index', compact(
            'student', 'academicYear', 'activeTerm', 'terms', 'selectedTerm',
            'assignedTeachers', 'submittedReviews', 'allReviews'
        ));
    }

    /**
     * Show the form to review a specific teacher for the selected term.
     */
    public function create(Request $request)
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('student.dashboard')->with('error', 'Student profile not found.');
        }

        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'term_id' => 'required|exists:terms,id',
        ]);

        $teacherId = $request->teacher_id;
        $termId = $request->term_id;

        $teacher = \App\Models\Teacher::findOrFail($teacherId);
        $term = Term::findOrFail($termId);

        // Check if already reviewed
        if (TeacherReview::hasReviewed($student->id, $teacherId, $termId)) {
            return redirect()->route('student.teacher-review.index')
                ->with('error', 'You have already reviewed this teacher for this term.');
        }

        $criteriaOptions = TeacherReview::criteriaOptions();
        $ratingScale = TeacherReview::ratingScale();

        return view('student.teacher-review.create', compact(
            'student', 'teacher', 'term', 'criteriaOptions', 'ratingScale'
        ));
    }

    /**
     * Store a new teacher review.
     */
    public function store(Request $request)
    {
        $student = $this->getStudent();
        if (!$student) {
            return redirect()->route('student.dashboard')->with('error', 'Student profile not found.');
        }

        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'term_id' => 'required|exists:terms,id',
            'teaching_quality' => 'required|integer|min:1|max:5',
            'communication' => 'required|integer|min:1|max:5',
            'punctuality' => 'required|integer|min:1|max:5',
            'subject_knowledge' => 'required|integer|min:1|max:5',
            'helpfulness' => 'required|integer|min:1|max:5',
            'fairness' => 'required|integer|min:1|max:5',
            'strengths' => 'nullable|string|max:2000',
            'areas_for_improvement' => 'nullable|string|max:2000',
            'additional_comments' => 'nullable|string|max:2000',
            'is_anonymous' => 'nullable|boolean',
        ]);

        // Check duplicate
        if (TeacherReview::hasReviewed($student->id, $validated['teacher_id'], $validated['term_id'])) {
            return redirect()->route('student.teacher-review.index')
                ->with('error', 'You have already reviewed this teacher for this term.');
        }

        $term = Term::findOrFail($validated['term_id']);

        $review = new TeacherReview($validated);
        $review->student_id = $student->id;
        $review->academic_year_id = $term->academic_year_id;
        $review->is_anonymous = $request->boolean('is_anonymous', true);
        $review->submitted_at = now();
        $review->status = 'submitted';
        $review->overall_score = $review->calculateOverallScore();
        $review->grade = $review->calculateGrade();
        $review->save();

        return redirect()->route('student.teacher-review.index')
            ->with('success', 'Your review has been submitted successfully. Thank you for your feedback!');
    }

    /**
     * Show a specific review.
     */
    public function show(TeacherReview $teacher_review)
    {
        $student = $this->getStudent();

        // Ensure this review belongs to the student
        if (!$student || $teacher_review->student_id !== $student->id) {
            abort(403, 'You can only view your own reviews.');
        }

        $teacher_review->load(['teacher', 'term', 'academicYear']);
        $criteriaOptions = TeacherReview::criteriaOptions();
        $gradeOptions = TeacherReview::gradeOptions();

        return view('student.teacher-review.show', compact(
            'teacher_review', 'criteriaOptions', 'gradeOptions'
        ));
    }
}
