<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Section;
use App\Models\Student;
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

        // Stats for the selected date
        $baseQuery = Attendance::where('date', $date);
        if ($classId) {
            $baseQuery->where('class_id', $classId);
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
        $classSummary = ClassRoom::withCount([
            'students as total_students' => fn($q) => $q->where('status', 'active'),
        ])->with(['sections'])->orderBy('name')->get()->map(function ($class) use ($date) {
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
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $classes = ClassRoom::orderBy('name')->get();

        return view('admin.attendance.index', compact(
            'date', 'classId', 'totalRecords', 'presentCount', 'absentCount',
            'lateCount', 'excusedCount', 'attendanceRate', 'classSummary',
            'recentRecords', 'classes'
        ));
    }

    // ── Create (Record Attendance) ────────────────────

    public function create(Request $request)
    {
        $classes = ClassRoom::with('sections')->orderBy('name')->get();
        $terms = Term::orderByDesc('created_at')->get();

        $selectedClass = $request->input('class_id');
        $selectedSection = $request->input('section_id');
        $selectedDate = $request->input('date', now()->toDateString());

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

        return view('admin.attendance.create', compact(
            'classes', 'terms', 'sections', 'students', 'existingAttendance',
            'selectedClass', 'selectedSection', 'selectedDate'
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
        $classes = ClassRoom::with('sections')->orderBy('name')->get();
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

        $classes = ClassRoom::whereIn('id', $byClass->keys())->orderBy('name')->get()->keyBy('id');

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

        if (!$classId) {
            return response()->json([]);
        }

        $query = Student::where('class_id', $classId)
            ->where('status', 'active');

        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        $students = $query->with(['classRoom', 'section'])
            ->orderBy('roll_number')
            ->get()
            ->map(fn($s) => [
                'id'              => $s->id,
                'first_name'      => $s->first_name,
                'last_name'       => $s->last_name,
                'full_name'       => trim($s->first_name . ' ' . $s->last_name),
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

        $records = $query->with(['student', 'classRoom', 'section', 'term'])
            ->orderBy('date', 'desc')
            ->orderBy('class_id')
            ->paginate(50)
            ->withQueryString();

        // Summary stats
        $totalRecords = Attendance::whereBetween('date', [$fromDate, $toDate])
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->count();

        $totalPresent = Attendance::whereBetween('date', [$fromDate, $toDate])
            ->where('status', 'present')
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->count();

        $totalAbsent = Attendance::whereBetween('date', [$fromDate, $toDate])
            ->where('status', 'absent')
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->count();

        $totalLate = Attendance::whereBetween('date', [$fromDate, $toDate])
            ->where('status', 'late')
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->count();

        $totalExcused = Attendance::whereBetween('date', [$fromDate, $toDate])
            ->where('status', 'excused')
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->count();

        $overallRate = $totalRecords > 0
            ? round(($totalPresent + $totalLate) / $totalRecords * 100, 1)
            : 0;

        $classes = ClassRoom::orderBy('name')->get();
        $sections = collect();
        if ($classId) {
            $sections = Section::where('class_id', $classId)->orderBy('name')->get();
        }

        return view('admin.attendance.report', compact(
            'fromDate', 'toDate', 'classId', 'sectionId',
            'records', 'totalRecords', 'totalPresent', 'totalAbsent',
            'totalLate', 'totalExcused', 'overallRate',
            'classes', 'sections'
        ));
    }
}
