<?php

namespace App\Http\Controllers\TeacherReassignment;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;

/**
 * TeacherReassignmentController
 *
 * Handles the bulk reassignment of teachers to sections (homeroom) and
 * subject assignments when a new academic year begins. This is the
 * follow-up workflow after the Academic Year Transition clears teacher IDs.
 */
class TeacherReassignmentController extends Controller
{
    /**
     * Show the teacher reassignment page for a given academic year.
     */
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $selectedAyId = $request->input('academic_year_id');
        $currentAy = AcademicYear::where('is_current', true)->first();

        // Default to current academic year if none selected
        if (!$selectedAyId && $currentAy) {
            $selectedAyId = $currentAy->id;
        }

        $selectedAy = $selectedAyId ? AcademicYear::find($selectedAyId) : null;

        // Get all active teachers for dropdowns
        $teachers = Teacher::where('status', '!=', 'inactive')
            ->orderBy('full_name')
            ->get();

        // Stats
        $stats = [
            'total_sections' => 0,
            'sections_with_teacher' => 0,
            'sections_without_teacher' => 0,
            'total_assignments' => 0,
            'assignments_with_teacher' => 0,
            'assignments_without_teacher' => 0,
        ];

        $classesWithSections = collect();
        $subjectAssignments = collect();

        if ($selectedAy) {
            // Get classes with sections for this academic year
            $classesWithSections = ClassRoom::where('academic_year_id', $selectedAy->id)
                ->with(['sections.teacher', 'branch'])
                ->orderBy('name')
                ->get();

            // Calculate section stats
            $allSections = $classesWithSections->flatMap->sections;
            $stats['total_sections'] = $allSections->count();
            $stats['sections_with_teacher'] = $allSections->where('teacher_id', '!=', null)->count();
            $stats['sections_without_teacher'] = $allSections->where('teacher_id', null)->count();

            // Get subject teacher assignments for this academic year
            $subjectAssignments = TeacherAssignment::where('academic_year_id', $selectedAy->id)
                ->with(['teacher', 'classRoom', 'section', 'subject'])
                ->orderBy('class_id')
                ->orderBy('section_id')
                ->orderBy('subject_id')
                ->get();

            $stats['total_assignments'] = $subjectAssignments->count();
            $stats['assignments_with_teacher'] = $subjectAssignments->where('teacher_id', '!=', null)->count();
            $stats['assignments_without_teacher'] = $subjectAssignments->where('teacher_id', null)->count();
        }

        return view('admin.TeacherReassignment.index', compact(
            'academicYears', 'selectedAy', 'selectedAyId', 'currentAy',
            'teachers', 'classesWithSections', 'subjectAssignments', 'stats'
        ));
    }

    /**
     * Bulk save homeroom teacher assignments for sections.
     */
    public function saveHomeroomTeachers(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:sections,id',
            'sections.*.teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $savedCount = 0;
        $clearedCount = 0;

        foreach ($validated['sections'] as $sectionData) {
            $section = Section::find($sectionData['id']);

            // Verify section belongs to a class in the selected academic year
            $classInAy = ClassRoom::where('id', $section->class_id)
                ->where('academic_year_id', $validated['academic_year_id'])
                ->exists();

            if (!$classInAy) {
                continue;
            }

            $oldTeacherId = $section->teacher_id;
            $newTeacherId = $sectionData['teacher_id'] ?: null;

            if ($oldTeacherId != $newTeacherId) {
                $section->teacher_id = $newTeacherId;
                $section->save();

                if ($newTeacherId) {
                    $savedCount++;
                } else {
                    $clearedCount++;
                }
            }
        }

        $message = "Homeroom teachers updated: {$savedCount} assigned";
        if ($clearedCount > 0) {
            $message .= ", {$clearedCount} cleared";
        }

        return redirect()->route('admin.teacher-reassignment.index', ['academic_year_id' => $validated['academic_year_id']])
            ->with('success', $message);
    }

    /**
     * Bulk save subject teacher assignments.
     */
    public function saveSubjectTeachers(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'assignments' => 'required|array',
            'assignments.*.id' => 'required|exists:teacher_assignments,id',
            'assignments.*.teacher_id' => 'nullable|exists:teachers,id',
        ]);

        $savedCount = 0;
        $clearedCount = 0;

        foreach ($validated['assignments'] as $assignmentData) {
            $assignment = TeacherAssignment::find($assignmentData['id']);

            // Verify assignment belongs to the selected academic year
            if ($assignment->academic_year_id != $validated['academic_year_id']) {
                continue;
            }

            $oldTeacherId = $assignment->teacher_id;
            $newTeacherId = $assignmentData['teacher_id'] ?: null;

            if ($oldTeacherId != $newTeacherId) {
                $assignment->teacher_id = $newTeacherId;
                $assignment->save();

                if ($newTeacherId) {
                    $savedCount++;
                } else {
                    $clearedCount++;
                }
            }
        }

        $message = "Subject teachers updated: {$savedCount} assigned";
        if ($clearedCount > 0) {
            $message .= ", {$clearedCount} cleared";
        }

        return redirect()->route('admin.teacher-reassignment.index', ['academic_year_id' => $validated['academic_year_id']])
            ->with('success', $message);
    }

    /**
     * Clear all teacher assignments for a given academic year.
     */
    public function clearAllTeachers(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
        ]);

        $ayId = $validated['academic_year_id'];

        // Clear homeroom teachers from sections
        $classIds = ClassRoom::where('academic_year_id', $ayId)->pluck('id');
        $sectionsCleared = Section::whereIn('class_id', $classIds)
            ->whereNotNull('teacher_id')
            ->update(['teacher_id' => null]);

        // Clear subject teacher assignments
        $assignmentsCleared = TeacherAssignment::where('academic_year_id', $ayId)
            ->whereNotNull('teacher_id')
            ->update(['teacher_id' => null]);

        $message = "All teachers cleared: {$sectionsCleared} homeroom assignments and {$assignmentsCleared} subject assignments.";

        return redirect()->route('admin.teacher-reassignment.index', ['academic_year_id' => $ayId])
            ->with('success', $message);
    }
}
