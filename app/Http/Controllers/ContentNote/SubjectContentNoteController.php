<?php

namespace App\Http\Controllers\ContentNote;

use App\Http\Controllers\Controller;
use App\Models\SubjectContentNote;
use App\Models\Subject;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\TeacherAssignment;
use App\Models\AcademicYear;
use App\Models\LessonPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectContentNoteController extends Controller
{
    // ── List Notes ─────────────────────────────────────────

    public function index(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();
        $activeAy = AcademicYear::where('is_current', true)->first();

        $query = SubjectContentNote::with(['subject', 'classroom', 'teacher', 'sections', 'lessonPlan', 'branch']);

        // Non-admin: only see own notes + shared notes
        if (!in_array($user->role, ['admin', 'super_admin', 'branch_principal', 'general_manager'])) {
            if ($teacher) {
                $query->where(function ($q) use ($teacher) {
                    $q->where('teacher_id', $teacher->id)
                      ->orWhere('is_shared', true);
                });
            }
        }

        // Filters
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('note_type')) {
            $query->where('note_type', $request->note_type);
        }
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }
        if ($request->filled('topic')) {
            $query->where('topic', 'like', "%{$request->topic}%");
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('topic', 'like', "%{$search}%")
                  ->orWhere('chapter', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($request->filled('is_shared')) {
            $query->where('is_shared', $request->boolean('is_shared'));
        }

        $notes = $query->orderBy('sort_order')->latest()->paginate(20);

        $subjects = $this->getTeacherSubjects($teacher, $activeAy);
        $classes = $this->getTeacherClasses($teacher, $activeAy);

        // Stats
        $totalNotes = SubjectContentNote::when($teacher, fn($q) => $q->where('teacher_id', $teacher->id))->count();
        $sharedNotes = SubjectContentNote::when($teacher, fn($q) => $q->where('teacher_id', $teacher->id))
            ->where('is_shared', true)->count();
        $linkedNotes = SubjectContentNote::when($teacher, fn($q) => $q->where('teacher_id', $teacher->id))
            ->whereNotNull('lesson_plan_id')->count();

        return view('admin.content-notes.index', compact(
            'notes', 'subjects', 'classes', 'teacher', 'activeAy',
            'totalNotes', 'sharedNotes', 'linkedNotes'
        ));
    }

    // ── Create Note ────────────────────────────────────────

    public function create()
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();
        $activeAy = AcademicYear::where('is_current', true)->first();

        $subjects = $this->getTeacherSubjects($teacher, $activeAy);
        $classes = $this->getTeacherClasses($teacher, $activeAy);

        // Lesson plans for linking (teacher's own or all for admin)
        $lessonPlans = $this->getTeacherLessonPlans($teacher, $activeAy);

        return view('admin.content-notes.create', compact(
            'subjects', 'classes', 'teacher', 'activeAy', 'lessonPlans'
        ));
    }

    // ── Store Note ─────────────────────────────────────────

    public function store(Request $request)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        $validated = $request->validate([
            'subject_id'       => 'required|exists:subjects,id',
            'class_id'         => 'required|exists:classes,id',
            'lesson_plan_id'   => 'nullable|exists:lesson_plans,id',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string|max:2000',
            'content'          => 'required|string|max:100000',
            'topic'            => 'nullable|string|max:255',
            'chapter'          => 'nullable|string|max:255',
            'note_type'        => 'required|in:general,summary,formula,definition,worked_example,reference',
            'difficulty'       => 'required|in:easy,medium,hard',
            'is_shared'        => 'nullable',
            'is_active'        => 'nullable',
            'section_ids'      => 'nullable|array',
            'section_ids.*'    => 'exists:sections,id',
        ]);

        $activeAy = AcademicYear::where('is_current', true)->first();
        if (!$activeAy) {
            $activeAy = AcademicYear::orderBy('id', 'desc')->first();
        }

        try {
            $noteData = [
                'subject_id'       => $validated['subject_id'],
                'class_id'         => $validated['class_id'],
                'lesson_plan_id'   => $validated['lesson_plan_id'] ?? null,
                'title'            => $validated['title'],
                'description'      => $validated['description'] ?? null,
                'content'          => $validated['content'],
                'topic'            => $validated['topic'] ?? null,
                'chapter'          => $validated['chapter'] ?? null,
                'note_type'        => $validated['note_type'],
                'difficulty'       => $validated['difficulty'],
                'is_shared'        => $request->has('is_shared'),
                'is_active'        => $request->has('is_active'),
                'sort_order'       => 0,
            ];

            if ($teacher) {
                $noteData['teacher_id'] = $teacher->id;
            }
            if ($activeAy) {
                $noteData['academic_year_id'] = $activeAy->id;
            }
            // Branch from user
            if ($user->branch_id) {
                $noteData['branch_id'] = $user->branch_id;
            }

            $note = SubjectContentNote::create($noteData);

            // Attach sections (many-to-many)
            if (!empty($validated['section_ids'])) {
                $note->sections()->attach($validated['section_ids']);
            }

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Content note save failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to save note. Database error: ' . $e->getMessage())->withInput();
        } catch (\Exception $e) {
            \Log::error('Content note save failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to save note: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('admin.content-notes.index')
            ->with('success', 'Content note created successfully.');
    }

    // ── Show Note ──────────────────────────────────────────

    public function show(SubjectContentNote $content_note)
    {
        $content_note->load(['subject', 'classroom', 'teacher', 'sections', 'lessonPlan', 'branch', 'academicYear']);

        return view('admin.content-notes.show', [
            'note' => $content_note,
        ]);
    }

    // ── Edit Note ──────────────────────────────────────────

    public function edit(SubjectContentNote $content_note)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        // Only creator or admin can edit
        if (!in_array($user->role, ['admin', 'super_admin']) && $content_note->teacher_id !== $teacher?->id) {
            abort(403, 'You can only edit your own notes.');
        }

        $content_note->load('sections');
        $activeAy = AcademicYear::where('is_current', true)->first();

        $subjects = $this->getTeacherSubjects($teacher, $activeAy);
        $classes = $this->getTeacherClasses($teacher, $activeAy);
        $lessonPlans = $this->getTeacherLessonPlans($teacher, $activeAy);

        // Get sections for the note's class
        $sections = Section::where('class_id', $content_note->class_id)->orderBy('name')->get();
        $selectedSections = $content_note->sections->pluck('id')->toArray();

        return view('admin.content-notes.edit', compact(
            'content_note', 'subjects', 'classes', 'teacher', 'activeAy',
            'lessonPlans', 'sections', 'selectedSections'
        ));
    }

    // ── Update Note ────────────────────────────────────────

    public function update(Request $request, SubjectContentNote $content_note)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        if (!in_array($user->role, ['admin', 'super_admin']) && $content_note->teacher_id !== $teacher?->id) {
            abort(403, 'You can only edit your own notes.');
        }

        $validated = $request->validate([
            'subject_id'       => 'required|exists:subjects,id',
            'class_id'         => 'required|exists:classes,id',
            'lesson_plan_id'   => 'nullable|exists:lesson_plans,id',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string|max:2000',
            'content'          => 'required|string|max:100000',
            'topic'            => 'nullable|string|max:255',
            'chapter'          => 'nullable|string|max:255',
            'note_type'        => 'required|in:general,summary,formula,definition,worked_example,reference',
            'difficulty'       => 'required|in:easy,medium,hard',
            'is_shared'        => 'nullable',
            'is_active'        => 'nullable',
            'section_ids'      => 'nullable|array',
            'section_ids.*'    => 'exists:sections,id',
        ]);

        try {
            $updateData = [
                'subject_id'       => $validated['subject_id'],
                'class_id'         => $validated['class_id'],
                'lesson_plan_id'   => $validated['lesson_plan_id'] ?? null,
                'title'            => $validated['title'],
                'description'      => $validated['description'] ?? null,
                'content'          => $validated['content'],
                'topic'            => $validated['topic'] ?? null,
                'chapter'          => $validated['chapter'] ?? null,
                'note_type'        => $validated['note_type'],
                'difficulty'       => $validated['difficulty'],
                'is_shared'        => $request->has('is_shared'),
                'is_active'        => $request->has('is_active'),
            ];

            $content_note->update($updateData);

            // Sync sections
            $sectionIds = $validated['section_ids'] ?? [];
            $content_note->sections()->sync($sectionIds);

        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Content note update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update note. Database error: ' . $e->getMessage())->withInput();
        } catch (\Exception $e) {
            \Log::error('Content note update failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to update note: ' . $e->getMessage())->withInput();
        }

        return redirect()->route('admin.content-notes.index')
            ->with('success', 'Content note updated successfully.');
    }

    // ── Delete Note ────────────────────────────────────────

    public function destroy(SubjectContentNote $content_note)
    {
        $user = Auth::user();
        $teacher = $user->teacherProfile ?? \App\Models\Teacher::where('email', $user->email)->first();

        if (!in_array($user->role, ['admin', 'super_admin']) && $content_note->teacher_id !== $teacher?->id) {
            abort(403, 'You can only delete your own notes.');
        }

        $content_note->delete();

        return back()->with('success', 'Content note deleted successfully.');
    }

    // ── Toggle Active ──────────────────────────────────────

    public function toggleActive(SubjectContentNote $content_note)
    {
        $content_note->update(['is_active' => !$content_note->is_active]);
        return back()->with('success', 'Note status updated.');
    }

    // ── Toggle Shared ──────────────────────────────────────

    public function toggleShared(SubjectContentNote $content_note)
    {
        $content_note->update(['is_shared' => !$content_note->is_shared]);
        return back()->with('success', $content_note->is_shared ? 'Note is now shared across all branches & sections.' : 'Note sharing disabled.');
    }

    // ── API: Get sections for a class ──────────────────────

    public function apiSections(Request $request)
    {
        $classId = $request->query('class_id');
        if (!$classId) {
            return response()->json([]);
        }

        $sections = Section::where('class_id', $classId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($sections);
    }

    // ── Helpers ─────────────────────────────────────────────

    private function getTeacherSubjects($teacher, $activeAy)
    {
        $user = Auth::user();
        if ($user && in_array($user->role, ['admin', 'super_admin', 'branch_principal', 'general_manager'])) {
            return Subject::orderBy('name')->get();
        }

        if (!$teacher) {
            return Subject::orderBy('name')->get();
        }

        $subjectIds = collect();

        if ($activeAy) {
            $subjectIds = TeacherAssignment::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeAy->id)
                ->pluck('subject_id')->unique();
        }

        if ($subjectIds->isEmpty()) {
            $subjectIds = TeacherAssignment::where('teacher_id', $teacher->id)
                ->pluck('subject_id')->unique();
        }

        if ($subjectIds->isEmpty() && $teacher->user_id) {
            $subjectIds = TeacherAssignment::where('teacher_id', $teacher->user_id)
                ->pluck('subject_id')->unique();
        }

        if ($subjectIds->isEmpty() && $teacher->email) {
            $userByEmail = \App\Models\User::where('email', $teacher->email)->first();
            if ($userByEmail) {
                $subjectIds = TeacherAssignment::where('teacher_id', $userByEmail->id)
                    ->pluck('subject_id')->unique();
            }
        }

        if ($subjectIds->isEmpty()) {
            return Subject::orderBy('name')->get();
        }

        return Subject::whereIn('id', $subjectIds)->orderBy('name')->get();
    }

    private function getTeacherClasses($teacher, $activeAy)
    {
        $user = Auth::user();
        if ($user && in_array($user->role, ['admin', 'super_admin', 'branch_principal', 'general_manager'])) {
            return ClassRoom::orderBy('name')->get();
        }

        if (!$teacher) {
            return ClassRoom::orderBy('name')->get();
        }

        $classIds = collect();

        if ($activeAy) {
            $classIds = TeacherAssignment::where('teacher_id', $teacher->id)
                ->where('academic_year_id', $activeAy->id)
                ->pluck('class_id')->unique();
        }

        if ($classIds->isEmpty()) {
            $classIds = TeacherAssignment::where('teacher_id', $teacher->id)
                ->pluck('class_id')->unique();
        }

        if ($classIds->isEmpty() && $teacher->user_id) {
            $classIds = TeacherAssignment::where('teacher_id', $teacher->user_id)
                ->pluck('class_id')->unique();
        }

        if ($classIds->isEmpty() && $teacher->email) {
            $userByEmail = \App\Models\User::where('email', $teacher->email)->first();
            if ($userByEmail) {
                $classIds = TeacherAssignment::where('teacher_id', $userByEmail->id)
                    ->pluck('class_id')->unique();
            }
        }

        if ($classIds->isEmpty()) {
            return ClassRoom::orderBy('name')->get();
        }

        return ClassRoom::whereIn('id', $classIds)->orderBy('name')->get();
    }

    private function getTeacherLessonPlans($teacher, $activeAy)
    {
        $user = Auth::user();

        if (in_array($user->role, ['admin', 'super_admin', 'branch_principal', 'general_manager'])) {
            return LessonPlan::with(['subject', 'classRoom'])
                ->when($activeAy, fn($q) => $q->where('academic_year_id', $activeAy->id))
                ->latest()
                ->limit(100)
                ->get();
        }

        if (!$teacher) {
            return collect();
        }

        return LessonPlan::with(['subject', 'classRoom'])
            ->where('teacher_id', $teacher->id)
            ->when($activeAy, fn($q) => $q->where('academic_year_id', $activeAy->id))
            ->latest()
            ->limit(50)
            ->get();
    }
}
