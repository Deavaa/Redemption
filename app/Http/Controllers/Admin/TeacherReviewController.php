<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Teacher;
use App\Models\TeacherReview;
use App\Models\Term;
use Illuminate\Http\Request;

class TeacherReviewController extends Controller
{
    /**
     * List all reviews with filters.
     */
    public function index(Request $request)
    {
        $query = TeacherReview::with(['student', 'teacher', 'term', 'academicYear']);

        // Filters
        if ($request->filled('term_id')) {
            $query->where('term_id', $request->term_id);
        }
        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }
        if ($request->filled('grade')) {
            $query->where('grade', $request->grade);
        }
        if ($request->filled('is_anonymous')) {
            $query->where('is_anonymous', $request->is_anonymous === '1');
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(25);

        // Filter options
        $academicYears = AcademicYear::orderBy('name', 'desc')->get();
        $terms = Term::with('academicYear')->orderBy('term_number')->get();
        $teachers = Teacher::orderBy('full_name')->get();

        // Stats
        $totalReviews = TeacherReview::count();
        $avgScore = TeacherReview::avg('overall_score');
        $thisTerm = Term::where('is_active', true)->first();
        $thisTermReviews = $thisTerm ? TeacherReview::where('term_id', $thisTerm->id)->count() : 0;

        return view('admin.teacher-reviews.index', compact(
            'reviews', 'academicYears', 'terms', 'teachers',
            'totalReviews', 'avgScore', 'thisTermReviews', 'thisTerm'
        ));
    }

    /**
     * Show a specific review.
     */
    public function show(TeacherReview $teacher_review)
    {
        $teacher_review->load(['student', 'teacher', 'term', 'academicYear']);
        $criteriaOptions = TeacherReview::criteriaOptions();
        $gradeOptions = TeacherReview::gradeOptions();

        return view('admin.teacher-reviews.show', compact(
            'teacher_review', 'criteriaOptions', 'gradeOptions'
        ));
    }

    /**
     * Show teacher review summary/aggregate for a specific term.
     */
    public function teacherSummary(Request $request)
    {
        $academicYear = AcademicYear::where('is_current', true)->first();
        $activeTerm = $academicYear
            ? Term::where('academic_year_id', $academicYear->id)->where('is_active', true)->first()
            : null;

        $selectedTermId = $request->get('term_id', $activeTerm?->id);
        $terms = $academicYear
            ? Term::where('academic_year_id', $academicYear->id)->orderBy('term_number')->get()
            : collect();

        // Get all teachers who have reviews for this term
        $teacherIds = TeacherReview::where('term_id', $selectedTermId)
            ->where('status', 'submitted')
            ->pluck('teacher_id')
            ->unique();

        $teachers = Teacher::whereIn('id', $teacherIds)
            ->withCount(['reviews' => fn($q) => $q->where('term_id', $selectedTermId)->where('status', 'submitted')])
            ->with(['reviews' => fn($q) => $q->where('term_id', $selectedTermId)->where('status', 'submitted')])
            ->orderBy('full_name')
            ->get()
            ->map(function ($teacher) use ($selectedTermId) {
                $reviews = $teacher->reviews;
                $criteria = TeacherReview::criteriaOptions();
                $averages = [];

                foreach (array_keys($criteria) as $criterion) {
                    $values = $reviews->pluck($criterion)->filter(fn($v) => $v > 0);
                    $averages[$criterion] = $values->count() > 0 ? round($values->avg(), 2) : 0;
                }

                $teacher->avg_scores = $averages;
                $teacher->avg_overall = round($reviews->avg('overall_score'), 2);
                $teacher->review_count = $reviews->count();

                // Determine grade
                $score = $teacher->avg_overall;
                if ($score >= 90) $teacher->avg_grade = 'excellent';
                elseif ($score >= 75) $teacher->avg_grade = 'good';
                elseif ($score >= 60) $teacher->avg_grade = 'satisfactory';
                elseif ($score >= 40) $teacher->avg_grade = 'needs_improvement';
                else $teacher->avg_grade = 'unsatisfactory';

                return $teacher;
            });

        $criteriaOptions = TeacherReview::criteriaOptions();
        $gradeOptions = TeacherReview::gradeOptions();

        return view('admin.teacher-reviews.summary', compact(
            'teachers', 'terms', 'selectedTermId', 'academicYear', 'activeTerm',
            'criteriaOptions', 'gradeOptions'
        ));
    }

    /**
     * Flag a review for inappropriate content.
     */
    public function flag(TeacherReview $teacher_review)
    {
        $teacher_review->update(['status' => 'flagged']);

        return back()->with('success', 'Review has been flagged for review.');
    }

    /**
     * Unflag a review.
     */
    public function unflag(TeacherReview $teacher_review)
    {
        $teacher_review->update(['status' => 'submitted']);

        return back()->with('success', 'Review has been unflagged.');
    }

    /**
     * Delete a review.
     */
    public function destroy(TeacherReview $teacher_review)
    {
        $teacher_review->delete();

        return back()->with('success', 'Review has been deleted.');
    }
}
