<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\AttendanceDelegation;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceDelegationController extends Controller
{
    /**
     * Display the delegation management page.
     * Only accessible by admin, branch_principal, and homeroom teachers.
     */
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $delegations = AttendanceDelegation::with(['classRoom', 'section', 'delegatedTeacher', 'delegatedBy'])
            ->where('date', $date)
            ->orderByDesc('created_at')
            ->get();

        $classes = ClassRoom::with(['sections', 'teacher'])->orderBy('numeric_name')->orderBy('name')->get();
        $teachers = Teacher::with('user')->orderBy('full_name')->get();

        return view('admin.attendance.delegation', compact(
            'date', 'delegations', 'classes', 'teachers'
        ));
    }

    /**
     * Store a new delegation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'delegated_to_teacher_id' => 'required|exists:teachers,id',
            'date' => 'required|date',
            'reason' => 'nullable|string|max:500',
        ]);

        // Verify the section belongs to the class
        if (!empty($validated['section_id'])) {
            $section = Section::where('id', $validated['section_id'])
                ->where('class_id', $validated['class_id'])
                ->first();
            if (!$section) {
                return back()->withErrors(['section_id' => 'The selected section does not belong to the selected class.'])->withInput();
            }
        }

        // Check for duplicate delegation
        $exists = AttendanceDelegation::where('class_id', $validated['class_id'])
            ->where('delegated_to_teacher_id', $validated['delegated_to_teacher_id'])
            ->where('date', $validated['date'])
            ->where('is_active', true)
            ->when($validated['section_id'] ?? null, fn($q) => $q->where('section_id', $validated['section_id']))
            ->when(!$validated['section_id'], fn($q) => $q->whereNull('section_id'))
            ->exists();

        if ($exists) {
            return back()->withErrors(['delegated_to_teacher_id' => 'This teacher is already delegated for this class/date.'])->withInput();
        }

        $validated['delegated_by_user_id'] = Auth::id();
        $validated['is_active'] = true;

        AttendanceDelegation::create($validated);

        return redirect()
            ->route('admin.attendance-delegation.index', ['date' => $validated['date']])
            ->with('success', 'Attendance delegation created successfully.');
    }

    /**
     * Revoke (deactivate) a delegation.
     */
    public function revoke(AttendanceDelegation $delegation)
    {
        $delegation->update(['is_active' => false]);

        return back()->with('success', 'Delegation revoked successfully.');
    }

    /**
     * Get sections for a class (AJAX endpoint).
     */
    public function apiSections(ClassRoom $class)
    {
        $sections = $class->sections()->with('teacher')->orderBy('name')->get();

        return response()->json($sections->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'teacher_name' => $s->teacher ? $s->teacher->full_name : null,
        ]));
    }
}
