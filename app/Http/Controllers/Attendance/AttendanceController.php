<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceDelegation;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    // ── Dashboard / Index ─────────────────────────────

    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $classId = $request->input('class_id');

        // For teachers, only show their assigned classes
        $user = Auth::user();
        $isTeacher = $user->role === 'teacher';
        $branchScope = $request->attributes->get('branch_scope');
        $teacherModel = null;
        $assignableClassIds = [];

        if ($isTeacher) {
            $teacherModel = Teacher::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();
            if ($teacherModel) {
                $assignableClassIds = AttendanceDelegation::getAssignableClasses($teacherModel->id, $date);
            }
        }

        // Stats for the selected date
        $baseQuery = Attendance::where('date', $date);
        if ($classId) {
            $baseQuery->where('class_id', $classId);
        }
        if ($isTeacher && $teacherModel) {
            $baseQuery->whereIn('class_id', $assignableClassIds);
        }
        // Branch scope: restrict attendance to branch students
        if ($branchScope) {
            $baseQuery->whereHas('student', fn($q) => $q->where('branch_id', $branchScope));
        }

        $totalRecords = (clone $baseQuery)->count();
        $presentCount = (clone $baseQuery)->where('status', 'present')->count();
        $absentCount  = (clone $baseQuery)->where('status', 'absent')->count();
        $lateCount    = (clone $baseQuery)->where('status', 'late')->count();
        $excusedCount = (clone $baseQuery)->where('status', 'excused')->count();

        $attendanceRate = $totalRecords > 0
            ? round(($presentCount + $lateCount) / $totalRecords * 100, 1)
            : 0;

        // Summary by class
        $classQuery = ClassRoom::withCount([
            'students as total_students' => fn($q) => $q->where('status', 'active'),
        ])->with(['sections']);

        // Filter classes for teachers
        if ($isTeacher && $teacherModel) {
            $classQuery->whereIn('id', $assignableClassIds);
        }
        // Branch scope: only show branch classes
        if ($branchScope) {
            $classQuery->where('branch_id', $branchScope);
        }

        $classSummary = $classQuery->orderBy('numeric_name')->orderBy('name')->get()->map(function ($class) use ($date) {
            $records = Attendance::where('date', $date)->where('class_id', $class->id)->get();
            $class->att_present = $records->where('status', 'present')->count();
            $class->att_absent  = $records->where('status', 'absent')->count();
            $class->att_late    = $records->where('status', 'late')->count();
            $class->att_excused = $records->where('status', 'excused')->count();
            $class->att_total   = $records->count();
            $class->att_rate    = $class->att_total > 0
                ? round(($class->att_present + $class->att_late) / $class->att_total * 100, 1)
                : null;
            return $class;
        });

        // Recent attendance records
        $recentRecords = Attendance::with(['student', 'classRoom', 'section', 'recorder'])
            ->where('date', $date)
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($isTeacher && $teacherModel, fn($q) => $q->whereIn('class_id', $assignableClassIds))
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $classes = ClassRoom::when($branchScope, fn($q) => $q->where('branch_id', $branchScope))
            ->orderBy('numeric_name')->orderBy('name')->get();
        if ($isTeacher && $teacherModel) {
            $classes = ClassRoom::whereIn('id', $assignableClassIds)
                ->when($branchScope, fn($q) => $q->where('branch_id', $branchScope))
                ->orderBy('numeric_name')->orderBy('name')->get();
        }

        return view('admin.attendance.index', compact(
            'date', 'classId', 'totalRecords', 'presentCount', 'absentCount',
            'lateCount', 'excusedCount', 'attendanceRate', 'classSummary',
            'recentRecords', 'classes', 'isTeacher', 'teacherModel'
        ));
    }

    // ── Create (Record Attendance) ────────────────────

    public function create(Request $request)
    {
        $user = Auth::user();
        $isTeacher = $user->role === 'teacher';
        $isAdmin = in_array($user->role, ['admin', 'full']);
        $isBranchPrincipal = $user->role === 'branch_principal';
        $isGeneralManager = $user->role === 'general_manager';
        $branchScope = $request->attributes->get('branch_scope');

        $teacherModel = null;
        $assignableClassIds = [];

        if ($isTeacher) {
            $teacherModel = Teacher::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();
            if (!$teacherModel) {
                return redirect()->route('admin.attendance.index')
                    ->with('error', 'No teacher profile found for your account.');
            }
        }

        $selectedClass = $request->input('class_id');
        $selectedSection = $request->input('section_id');
        $selectedDate = $request->input('date', now()->toDateString());

        // For teachers, enforce homeroom/delegation check
        if ($isTeacher && $selectedClass) {
            $canTake = AttendanceDelegation::canTakeAttendance(
                $teacherModel->id, $selectedClass, $selectedSection, $selectedDate
            );
            if (!$canTake) {
                return redirect()->route('admin.attendance.index')
                    ->with('error', 'You are not authorized to take attendance for this class. Only homeroom teachers or delegated teachers can take attendance.');
            }
        }

        // Get available classes
        if ($isTeacher && $teacherModel) {
            $assignableClassIds = AttendanceDelegation::getAssignableClasses($teacherModel->id, $selectedDate);
            $classes = ClassRoom::whereIn('id', $assignableClassIds)->with('sections')
                ->when($branchScope, fn($q) => $q->where('branch_id', $branchScope))
                ->orderBy('numeric_name')->orderBy('name')->get();
        } else {
            $classes = ClassRoom::with('sections')
                ->when($branchScope, fn($q) => $q->where('branch_id', $branchScope))
                ->orderBy('numeric_name')->orderBy('name')->get();
        }

        $terms = Term::orderByDesc('created_at')->get();

        $students = collect();
        $existingAttendance = collect();

        if ($selectedClass) {
            $query = Student::where('class_id', $selectedClass)
                ->where('status', 'active');

            if ($selectedSection) {
                $query->where('section_id', $selectedSection);
            }

            $students = $query->with(['classRoom', 'section'])->orderBy('roll_number')->get();

            // Load existing attendance for these students on the selected date
            if ($students->isNotEmpty()) {
                $existingAttendance = Attendance::where('date', $selectedDate)
                    ->whereIn('student_id', $students->pluck('id'))
                    ->get()
                    ->keyBy('student_id');
            }
        }

        $sections = collect();
        if ($selectedClass) {
            $sections = Section::where('class_id', $selectedClass)->orderBy('name')->get();
        }

        // Check if current user is a homeroom teacher for the selected class
        $isHomeroomForClass = false;
        $delegationInfo = null;
        if ($isTeacher && $teacherModel && $selectedClass) {
            $isHomeroomForClass = ClassRoom::where('id', $selectedClass)
                ->where('teacher_id', $teacherModel->id)
                ->exists();

            if (!$isHomeroomForClass && $selectedSection) {
                $isHomeroomForClass = Section::where('id', $selectedSection)
                    ->where('teacher_id', $teacherModel->id)
                    ->exists();
            }

            if (!$isHomeroomForClass) {
                $delegationInfo = AttendanceDelegation::where('class_id', $selectedClass)
                    ->where('delegated_to_teacher_id', $teacherModel->id)
                    ->where('date', $selectedDate)
                    ->where('is_active', true)
                    ->first();
            }
        }

        return view('admin.attendance.create', compact(
            'classes', 'terms', 'sections', 'students', 'existingAttendance',
            'selectedClass', 'selectedSection', 'selectedDate',
            'isTeacher', 'teacherModel', 'isHomeroomForClass', 'delegationInfo'
        ));
    }

    // ── Store ─────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'date'          => 'required|date',
            'class_id'      => 'required|exists:classes,id',
            'section_id'    => 'nullable|exists:sections,id',
            'term_id'       => 'nullable|exists:terms,id',
            'students'      => 'required|array',
            'students.*.student_id' => 'required|exists:students,id',
            'students.*.status'     => 'required|in:present,absent,late,excused',
            'students.*.remarks'    => 'nullable|string|max:500',
        ]);

        $date = $request->input('date');
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $termId = $request->input('term_id');
        $recordedBy = Auth::id();

        // For teachers, verify they can take attendance for this class
        $user = Auth::user();
        if ($user->role === 'teacher') {
            $teacherModel = Teacher::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();
            if ($teacherModel) {
                $canTake = AttendanceDelegation::canTakeAttendance(
                    $teacherModel->id, $classId, $sectionId, $date
                );
                if (!$canTake) {
                    return redirect()->route('admin.attendance.index')
                        ->with('error', 'You are not authorized to take attendance for this class.');
                }
            }
        }

        $upsertData = [];

        foreach ($request->input('students') as $studentData) {
            $upsertData[] = [
                'student_id'   => $studentData['student_id'],
                'class_id'     => $classId,
                'section_id'   => $sectionId,
                'term_id'      => $termId,
                'date'         => $date,
                'status'       => $studentData['status'],
                'remarks'      => $studentData['remarks'] ?? null,
                'recorded_by'  => $recordedBy,
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        // Upsert based on unique constraint (student_id, date)
        Attendance::upsert(
            $upsertData,
            ['student_id', 'date'],
            ['class_id', 'section_id', 'term_id', 'status', 'remarks', 'recorded_by', 'updated_at']
        );

        return redirect()
            ->route('admin.attendance.show', ['date' => $date])
            ->with('success', 'Attendance recorded successfully.');
    }

    // ── Edit ──────────────────────────────────────────

    public function edit($date, $classId)
    {
        $branchScope = request()->attributes->get('branch_scope');
        $classes = ClassRoom::with('sections')
            ->when($branchScope, fn($q) => $q->where('branch_id', $branchScope))
            ->orderBy('numeric_name')->orderBy('name')->get();
        $terms = Term::orderByDesc('created_at')->get();

        $records = Attendance::with(['student', 'classRoom', 'section'])
            ->where('date', $date)
            ->where('class_id', $classId)
            ->get();

        if ($records->isEmpty()) {
            return redirect()->route('admin.attendance.index')
                ->with('error', 'No attendance records found for this date and class.');
        }

        $classRoom = ClassRoom::findOrFail($classId);
        $sections = Section::where('class_id', $classId)->orderBy('name')->get();

        // Get all active students in the class for the edit form
        $students = Student::where('class_id', $classId)
            ->where('status', 'active')
            ->with(['classRoom', 'section'])
            ->orderBy('roll_number')
            ->get();

        // Map existing attendance
        $existingAttendance = $records->keyBy('student_id');

        return view('admin.attendance.edit', compact(
            'date', 'classId', 'classRoom', 'classes', 'terms', 'sections',
            'students', 'existingAttendance', 'records'
        ));
    }

    // ── Update ────────────────────────────────────────

    public function update(Request $request)
    {
        $request->validate([
            'date'          => 'required|date',
            'class_id'      => 'required|exists:classes,id',
            'students'      => 'required|array',
            'students.*.student_id' => 'required|exists:students,id',
            'students.*.status'     => 'required|in:present,absent,late,excused',
            'students.*.remarks'    => 'nullable|string|max:500',
        ]);

        $date = $request->input('date');
        $classId = $request->input('class_id');
        $recordedBy = Auth::id();

        foreach ($request->input('students') as $studentData) {
            Attendance::where('student_id', $studentData['student_id'])
                ->where('date', $date)
                ->update([
                    'status'      => $studentData['status'],
                    'remarks'     => $studentData['remarks'] ?? null,
                    'recorded_by' => $recordedBy,
                ]);
        }

        return redirect()
            ->route('admin.attendance.show', ['date' => $date])
            ->with('success', 'Attendance updated successfully.');
    }

    // ── Show (Day Detail) ─────────────────────────────

    public function show($date)
    {
        $records = Attendance::with(['student', 'classRoom', 'section', 'term', 'recorder'])
            ->where('date', $date)
            ->orderBy('class_id')
            ->orderBy('student_id')
            ->get();

        $totalRecords = $records->count();
        $presentCount = $records->where('status', 'present')->count();
        $absentCount  = $records->where('status', 'absent')->count();
        $lateCount    = $records->where('status', 'late')->count();
        $excusedCount = $records->where('status', 'excused')->count();
        $attendanceRate = $totalRecords > 0
            ? round(($presentCount + $lateCount) / $totalRecords * 100, 1)
            : 0;

        // Group by class
        $byClass = $records->groupBy('class_id');

        $classes = ClassRoom::whereIn('id', $byClass->keys())->orderBy('numeric_name')->orderBy('name')->get()->keyBy('id');

        return view('admin.attendance.show', compact(
            'date', 'records', 'totalRecords', 'presentCount', 'absentCount',
            'lateCount', 'excusedCount', 'attendanceRate', 'byClass', 'classes'
        ));
    }

    // ── API: Students for AJAX ────────────────────────

    public function apiStudents(Request $request)
    {
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $branchScope = $request->attributes->get('branch_scope');

        if (!$classId) {
            return response()->json([]);
        }

        $query = Student::where('class_id', $classId)
            ->where('status', 'active');

        // Branch scope: restrict to branch students
        if ($branchScope) {
            $query->where('branch_id', $branchScope);
        }

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        $students = $query->with(['classRoom', 'section'])
            ->orderBy('roll_number')
            ->get()
            ->map(fn($s) => [
                'id'              => $s->id,
                'full_name'       => $s->full_name,
                'roll_number'     => $s->roll_number,
                'admission_number'=> $s->admission_number,
                'class_name'      => $s->classRoom?->name,
                'section_name'    => $s->section?->name,
            ]);

        return response()->json($students);
    }

    // ── Report ────────────────────────────────────────

    public function report(Request $request)
    {
        $fromDate  = $request->input('from_date', now()->subDays(30)->toDateString());
        $toDate    = $request->input('to_date', now()->toDateString());
        $classId   = $request->input('class_id');
        $sectionId = $request->input('section_id');

        $query = Attendance::whereBetween('date', [$fromDate, $toDate]);

        if ($classId) {
            $query->where('class_id', $classId);
        }
        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        // ── Privilege-based filtering ──
        $user = Auth::user();
        $isBranchPrincipal = $user->role === 'branch_principal';
        $isTeacher = $user->role === 'teacher';
        $isStudent = $user->role === 'student';
        $isParent = $user->role === 'parent';

        // Branch principal: restrict to their branch
        if ($isBranchPrincipal && $user->branch_id) {
            $query->whereHas('student', function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }

        // Teacher: restrict to assigned classes
        if ($isTeacher) {
            $teacherModel = Teacher::where('user_id', $user->id)
                ->orWhere('email', $user->email)->first();
            if ($teacherModel) {
                $assignableClassIds = AttendanceDelegation::getAssignableClasses($teacherModel->id, $fromDate);
                $query->whereIn('class_id', $assignableClassIds);
            }
        }

        // Student: show only their own attendance
        if ($isStudent) {
            $studentModel = Student::where('user_id', $user->id)->first();
            if ($studentModel) {
                $query->where('student_id', $studentModel->id);
            }
        }

        // Parent: show only their children's attendance
        if ($isParent) {
            $parentModel = \App\Models\ParentModel::where('user_id', $user->id)->first();
            if ($parentModel) {
                $childIds = $parentModel->students()->pluck('students.id');
                $query->whereIn('student_id', $childIds);
            }
        }

        $records = $query->with(['student', 'classRoom', 'section', 'term'])
            ->orderBy('date', 'desc')
            ->orderBy('class_id')
            ->paginate(50)
            ->withQueryString();

        // Summary stats (with same privilege filtering)
        $statsBaseQuery = Attendance::whereBetween('date', [$fromDate, $toDate])
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId));

        // Apply privilege filtering to summary stats
        if ($isBranchPrincipal && $user->branch_id) {
            $statsBaseQuery->whereHas('student', function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            });
        }
        if ($isTeacher) {
            $teacherModel = $teacherModel ?? Teacher::where('user_id', $user->id)
                ->orWhere('email', $user->email)->first();
            if ($teacherModel) {
                $assignableClassIds = $assignableClassIds ?? AttendanceDelegation::getAssignableClasses($teacherModel->id, $fromDate);
                $statsBaseQuery->whereIn('class_id', $assignableClassIds);
            }
        }
        if ($isStudent) {
            $studentModel = $studentModel ?? Student::where('user_id', $user->id)->first();
            if ($studentModel) {
                $statsBaseQuery->where('student_id', $studentModel->id);
            }
        }
        if ($isParent) {
            $parentModel = $parentModel ?? \App\Models\ParentModel::where('user_id', $user->id)->first();
            if ($parentModel) {
                $childIds = $childIds ?? $parentModel->students()->pluck('students.id');
                $statsBaseQuery->whereIn('student_id', $childIds);
            }
        }

        $totalRecords = (clone $statsBaseQuery)->count();
        $totalPresent = (clone $statsBaseQuery)->where('status', 'present')->count();
        $totalAbsent  = (clone $statsBaseQuery)->where('status', 'absent')->count();
        $totalLate    = (clone $statsBaseQuery)->where('status', 'late')->count();
        $totalExcused = (clone $statsBaseQuery)->where('status', 'excused')->count();

        $overallRate = $totalRecords > 0
            ? round(($totalPresent + $totalLate) / $totalRecords * 100, 1)
            : 0;

        // Student/parent context variables
        $studentName = null;
        $childNames = [];
        if ($isStudent) {
            $studentModel = $studentModel ?? Student::where('user_id', $user->id)->first();
            $studentName = $studentModel?->full_name;
        }
        if ($isParent) {
            $parentModel = $parentModel ?? \App\Models\ParentModel::where('user_id', $user->id)->first();
            if ($parentModel) {
                $childNames = $parentModel->students()->pluck('full_name')->toArray();
            }
        }

        $branchScope = $request->attributes->get('branch_scope');
        $classes = ClassRoom::when($branchScope, fn($q) => $q->where('branch_id', $branchScope))
            ->orderBy('numeric_name')->orderBy('name')->get();
        $sections = collect();
        if ($classId) {
            $sections = Section::where('class_id', $classId)->orderBy('name')->get();
        }

        return view('admin.attendance.report', compact(
            'fromDate', 'toDate', 'classId', 'sectionId',
            'records', 'totalRecords', 'totalPresent', 'totalAbsent',
            'totalLate', 'totalExcused', 'overallRate',
            'classes', 'sections', 'studentName', 'childNames'
        ));
    }
}
