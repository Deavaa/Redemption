<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentComment;
use Illuminate\Http\Request;

class StudentCommentController extends Controller
{
    /**
     * Display comments for a student.
     */
    public function index(Student $student)
    {
        $this->authorizeCommentView();

        $academicYearId = request()->get('academic_year_id');
        $commentType = request()->get('comment_type');

        $query = StudentComment::with(['user', 'academicYear'])
            ->where('student_id', $student->id);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($commentType) {
            $query->where('comment_type', $commentType);
        }

        // Visibility filter: non-admins can't see private comments from other users
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'general_manager'])) {
            $query->where(function ($q) use ($user) {
                $q->where('visibility', '!=', 'private')
                    ->orWhere('user_id', $user->id);
            });
        }

        $comments = $query->latestFirst()->paginate(20);
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();

        return response()->json([
            'comments' => $comments->map(fn ($c) => [
                'id' => $c->id,
                'comment' => $c->comment,
                'comment_type' => $c->comment_type,
                'comment_type_label' => $c->comment_type_label,
                'visibility' => $c->visibility,
                'visibility_label' => $c->visibility_label,
                'is_report_comment' => $c->is_report_comment,
                'author_name' => $c->author_name,
                'author_role' => $c->author_role,
                'academic_year' => $c->academicYear?->name,
                'created_at' => $c->created_at->format('M d, Y h:i A'),
                'can_delete' => $c->user_id === $user->id || in_array($user->role, ['admin', 'general_manager']),
            ]),
            'academic_years' => $academicYears,
            'pagination' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'total' => $comments->total(),
            ],
        ]);
    }

    /**
     * Store a new comment.
     */
    public function store(Request $request, Student $student)
    {
        $this->authorizeCommentCreate();

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
            'comment_type' => 'required|in:general,academic,behavior,attendance,progress',
            'visibility' => 'required|in:private,staff,public',
            'is_report_comment' => 'nullable|boolean',
            'academic_year_id' => 'nullable|exists:academic_years,id',
        ]);

        $comment = StudentComment::create([
            'student_id' => $student->id,
            'user_id' => auth()->id(),
            'academic_year_id' => $validated['academic_year_id'] ?? AcademicYear::where('is_current', true)->value('id'),
            'comment_type' => $validated['comment_type'],
            'visibility' => $validated['visibility'],
            'comment' => $validated['comment'],
            'is_report_comment' => $validated['is_report_comment'] ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully.',
            'comment' => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'comment_type' => $comment->comment_type,
                'comment_type_label' => $comment->comment_type_label,
                'visibility' => $comment->visibility,
                'visibility_label' => $comment->visibility_label,
                'is_report_comment' => $comment->is_report_comment,
                'author_name' => $comment->author_name,
                'author_role' => $comment->author_role,
                'academic_year' => $comment->academicYear?->name,
                'created_at' => $comment->created_at->format('M d, Y h:i A'),
            ],
        ], 201);
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, Student $student, StudentComment $comment)
    {
        // Only the author or admin can edit
        if ($comment->user_id !== auth()->id() && !in_array(auth()->user()->role, ['admin', 'general_manager'])) {
            abort(403, 'You can only edit your own comments.');
        }

        if ($comment->student_id !== $student->id) {
            abort(404);
        }

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
            'comment_type' => 'required|in:general,academic,behavior,attendance,progress',
            'visibility' => 'required|in:private,staff,public',
            'is_report_comment' => 'nullable|boolean',
        ]);

        $comment->update([
            'comment' => $validated['comment'],
            'comment_type' => $validated['comment_type'],
            'visibility' => $validated['visibility'],
            'is_report_comment' => $validated['is_report_comment'] ?? $comment->is_report_comment,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully.',
        ]);
    }

    /**
     * Delete a comment.
     */
    public function destroy(Student $student, StudentComment $comment)
    {
        // Only the author or admin can delete
        if ($comment->user_id !== auth()->id() && !in_array(auth()->user()->role, ['admin', 'general_manager'])) {
            abort(403, 'You can only delete your own comments.');
        }

        if ($comment->student_id !== $student->id) {
            abort(404);
        }

        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully.',
        ]);
    }

    /**
     * Get report comments for a student (for report card generation).
     */
    public function reportComments(Student $student)
    {
        $academicYearId = request()->get('academic_year_id');

        $query = StudentComment::with(['user'])
            ->where('student_id', $student->id)
            ->where('is_report_comment', true)
            ->whereIn('visibility', ['staff', 'public']);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $comments = $query->latestFirst()->get();

        return response()->json([
            'comments' => $comments->map(fn ($c) => [
                'id' => $c->id,
                'comment' => $c->comment,
                'comment_type' => $c->comment_type,
                'comment_type_label' => $c->comment_type_label,
                'author_name' => $c->author_name,
                'author_role' => $c->author_role,
                'created_at' => $c->created_at->format('M d, Y'),
            ]),
        ]);
    }

    private function authorizeCommentView(): void
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'general_manager', 'branch_principal', 'teacher', 'registrar'])) {
            abort(403, 'You are not authorized to view student comments.');
        }
    }

    private function authorizeCommentCreate(): void
    {
        $user = auth()->user();
        if (!in_array($user->role, ['admin', 'general_manager', 'branch_principal', 'teacher', 'registrar'])) {
            abort(403, 'You are not authorized to add student comments.');
        }
    }
}
