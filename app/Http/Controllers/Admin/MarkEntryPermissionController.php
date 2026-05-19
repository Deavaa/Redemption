<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\MarkEntryPermission;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\Term;
use App\Models\Student;
use Illuminate\Http\Request;

class MarkEntryPermissionController extends Controller
{
    /**
     * Display mark entry permissions management.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Only branch_principal, admin, super_admin can manage permissions
        if (!in_array($user->role, ['admin', 'super_admin', 'branch_principal'])) {
            abort(403, 'You are not authorized to manage mark entry permissions.');
        }

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $currentAy = AcademicYear::where('is_current', true)->first();
        $teachers = Teacher::where('status', 'active')->orderBy('full_name')->get();

        $selectedAy = $request->filled('academic_year_id') ? AcademicYear::find($request->academic_year_id) : $currentAy;
        $selectedTerm = $request->filled('term_id') ? Term::find($request->term_id) : null;
        $selectedTeacher = $request->filled('teacher_id') ? Teacher::find($request->teacher_id) : null;

        $terms = $selectedAy ? Term::where('academic_year_id', $selectedAy->id)->orderBy('id')->get() : collect();

        // Get permissions
        $query = MarkEntryPermission::with(['teacher', 'student', 'subject', 'academicYear', 'term', 'grantedBy']);

        if ($selectedAy) {
            $query->where('academic_year_id', $selectedAy->id);
        }
        if ($selectedTerm) {
            $query->where('term_id', $selectedTerm->id);
        }
        if ($selectedTeacher) {
            $query->where('teacher_id', $selectedTeacher->id);
        }

        $permissions = $query->orderBy('id', 'desc')->paginate(30);

        return view('admin.mark_entry_permissions.index', compact(
            'academicYears', 'terms', 'teachers', 'permissions',
            'selectedAy', 'selectedTerm', 'selectedTeacher'
        ));
    }

    /**
     * Show form to create a new permission.
     */
    public function create()
    {
        $teachers = Teacher::where('status', 'active')->orderBy('full_name')->get();
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $currentAy = AcademicYear::where('is_current', true)->first();
        $terms = $currentAy ? Term::where('academic_year_id', $currentAy->id)->orderBy('id')->get() : collect();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('admin.mark_entry_permissions.create', compact(
            'teachers', 'academicYears', 'terms', 'subjects', 'currentAy'
        ));
    }

    /**
     * Store a new mark entry permission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'reason' => 'required|string|max:500',
            'expires_at' => 'nullable|date|after:now',
        ]);

        // Check if permission already exists
        $exists = MarkEntryPermission::where('teacher_id', $validated['teacher_id'])
            ->where('student_id', $validated['student_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('term_id', $validated['term_id'])
            ->where('is_active', true)
            ->exists();

        if ($exists) {
            return redirect()->back()->withInput()
                ->with('error', 'An active permission already exists for this teacher-student-subject-term combination.');
        }

        // Verify that mark entry is locked (permissions are only relevant when locked)
        $student = Student::find($validated['student_id']);
        $isLocked = \App\Models\MarkEntryLock::isLocked(
            $student->branch_id,
            $validated['academic_year_id'],
            $validated['term_id']
        );

        MarkEntryPermission::create([
            'teacher_id' => $validated['teacher_id'],
            'student_id' => $validated['student_id'],
            'subject_id' => $validated['subject_id'],
            'academic_year_id' => $validated['academic_year_id'],
            'term_id' => $validated['term_id'],
            'granted_by' => auth()->id(),
            'reason' => $validated['reason'],
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => true,
        ]);

        $lockMsg = $isLocked
            ? 'Mark entry is currently locked. This teacher will be able to edit this specific student\'s mark.'
            : 'Note: Mark entry is not currently locked. This permission will be relevant when the entry is locked.';

        return redirect()->route('admin.mark-entry-permissions.index')
            ->with('success', 'Mark edit permission granted successfully. ' . $lockMsg);
    }

    /**
     * Revoke a mark entry permission.
     */
    public function revoke($id)
    {
        $permission = MarkEntryPermission::findOrFail($id);
        $permission->update(['is_active' => false]);
        return redirect()->back()->with('success', 'Mark edit permission has been revoked.');
    }

    /**
     * Get students for a class (API for cascading dropdowns).
     */
    public function apiStudents(Request $request)
    {
        $request->validate(['class_id' => 'required|exists:classrooms,id']);
        $students = Student::where('class_id', $request->class_id)
            ->where('status', 'active')
            ->select('id', 'full_name', 'roll_number')
            ->orderBy('full_name')
            ->get();
        return response()->json($students);
    }

    /**
     * Get teacher's assigned subjects (API).
     */
    public function apiTeacherSubjects(Request $request)
    {
        $request->validate(['teacher_id' => 'required|exists:teachers,id']);
        $teacher = Teacher::find($request->teacher_id);
        $subjects = $teacher->assignments()
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->unique('id')
            ->filter()
            ->values();
        return response()->json($subjects);
    }

    /**
     * Batch create permissions for a teacher.
     */
    public function batchStore(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'subject_ids' => 'required|array',
            'subject_ids.*' => 'exists:subjects,id',
            'reason' => 'required|string|max:500',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $created = 0;
        $skipped = 0;

        foreach ($validated['student_ids'] as $studentId) {
            foreach ($validated['subject_ids'] as $subjectId) {
                $exists = MarkEntryPermission::where('teacher_id', $validated['teacher_id'])
                    ->where('student_id', $studentId)
                    ->where('subject_id', $subjectId)
                    ->where('academic_year_id', $validated['academic_year_id'])
                    ->where('term_id', $validated['term_id'])
                    ->where('is_active', true)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                MarkEntryPermission::create([
                    'teacher_id' => $validated['teacher_id'],
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'academic_year_id' => $validated['academic_year_id'],
                    'term_id' => $validated['term_id'],
                    'granted_by' => auth()->id(),
                    'reason' => $validated['reason'],
                    'expires_at' => $validated['expires_at'] ?? null,
                    'is_active' => true,
                ]);
                $created++;
            }
        }

        return redirect()->route('admin.mark-entry-permissions.index')
            ->with('success', "Permissions created: {$created}. Skipped (already exist): {$skipped}.");
    }
}
