<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Classroom;
use App\Models\ParentModel;
use App\Models\Section;
use App\Models\Student;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $statusFilter = $request->get('status', '');

        $query = Student::with(['classroom', 'section']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                    ->orWhere('admission_number', 'LIKE', "%{$search}%")
                    ->orWhere('roll_number', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        if ($statusFilter && in_array($statusFilter, ['active', 'inactive', 'transferred', 'graduated'])) {
            $query->where('status', $statusFilter);
        }

        $students = $query->orderBy('full_name')->paginate(25)->withQueryString();

        $totalStudents = Student::count();
        $activeStudents = Student::where('status', 'active')->count();
        $inactiveStudents = Student::where('status', 'inactive')->count();

        return view('admin.Student.index', compact('students', 'totalStudents', 'activeStudents', 'inactiveStudents', 'search', 'statusFilter'));
    }

    public function create()
    {
        $classrooms = Classroom::with('sections', 'branch')->get();
        $parents = ParentModel::orderBy('father_name')->get(['id', \DB::raw('father_name as name'), \DB::raw('father_phone as phone')]);
        $branches = Branch::all();
        $academicYears = AcademicYear::all();
        if ($academicYears->isEmpty()) {
            AcademicYear::create(['name' => '2024-2025']);
            AcademicYear::create(['name' => '2025-2026']);
            $academicYears = AcademicYear::all();
        }

        // Pre-generate the next admission number for preview
        $nextAdmissionNumber = $this->generateAdmissionNumber();

        return view('admin.Student.create', compact('classrooms', 'parents', 'branches', 'academicYears', 'nextAdmissionNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:students,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'admission_number' => 'nullable|string|max:50|unique:students,admission_number',
            'roll_number' => 'nullable|string|max:50',
            'admission_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,graduated,transferred',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('student-photos', 'public');
            $validated['photo'] = $photoPath;
        }

        // Get class_id from section_id
        $section = Section::find($validated['section_id']);
        $validated['class_id'] = $section->class_id;

        // Generate admission_number if not provided
        if (empty($validated['admission_number'])) {
            $validated['admission_number'] = $this->generateAdmissionNumber();
        }

        // Generate roll_number if not provided
        if (empty($validated['roll_number'])) {
            $validated['roll_number'] = $this->generateRollNumber($validated['section_id']);
        }

        // Auto-generate student ID number using the current academic year
        $idNumber = $request->input('id_number');
        if (empty($idNumber)) {
            $year = $this->getAyStartYear($this->getCurrentAcademicYear());
            $lastStudent = \App\Models\User::where('id_number', 'LIKE', "STD-{$year}-%")
                ->orderBy('id_number', 'desc')->first();
            $nextNum = 1;
            if ($lastStudent && $lastStudent->id_number) {
                $parts = explode('-', $lastStudent->id_number);
                $nextNum = (int)end($parts) + 1;
            }
            $idNumber = "STD-{$year}-" . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
        }

        // Create user account for student
        $defaultPassword = $request->filled('date_of_birth') 
            ? str_replace('-', '', $request->date_of_birth) 
            : 'Student@' . rand(1000, 9999);

        $user = \App\Models\User::create([
            'name' => $validated['full_name'],
            'email' => $validated['email'] ?? $idNumber . '@redemption.edu',
            'id_number' => $idNumber,
            'password' => bcrypt($defaultPassword),
            'role' => 'student',
            'is_active' => true,
        ]);

        $validated['user_id'] = $user->id;

        // Set default admission_date if not provided
        if (empty($validated['admission_date'])) {
            $validated['admission_date'] = now()->toDateString();
        }

        Student::create($validated);

        return redirect()->route('admin.students.index')->with('success', 'Student enrolled successfully!');
    }

    public function show(Student $student)
    {
        $student->load(['classroom', 'section', 'branch', 'academicYear', 'parents']);
        return view('admin.Student.show', ['data' => $student]);
    }

    public function edit(Student $student)
    {
        $classrooms = Classroom::with('sections', 'branch')->get();
        $parents = ParentModel::orderBy('father_name')->get(['id', \DB::raw('father_name as name'), \DB::raw('father_phone as phone')]);
        $branches = Branch::all();
        $academicYears = AcademicYear::all();
        $sections = Section::all();
        if ($academicYears->isEmpty()) {
            AcademicYear::create(['name' => '2024-2025']);
            AcademicYear::create(['name' => '2025-2026']);
            $academicYears = AcademicYear::all();
        }

        return view('admin.Student.edit', [
            'data' => $student,
            'classrooms' => $classrooms,
            'parents' => $parents,
            'branches' => $branches,
            'academicYears' => $academicYears,
            'sections' => $sections,
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:students,email,'.$student->id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'section_id' => 'required|exists:sections,id',
            'admission_number' => 'required|string|max:50|unique:students,admission_number,'.$student->id,
            'roll_number' => 'nullable|string|max:50',
            'admission_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,graduated,transferred',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'guardian_id' => 'nullable|integer|exists:parents,id',
            'new_comment' => 'nullable|string|max:2000',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('student-photos', 'public');
            $validated['photo'] = $photoPath;
        }

        // Get class_id from section_id
        $section = Section::find($validated['section_id']);
        $validated['class_id'] = $section->class_id;

        if (empty($validated['admission_date'])) {
            $validated['admission_date'] = now()->toDateString();
        }

        // Extract non-fillable fields before update
        $guardianId = $validated['guardian_id'] ?? null;
        $newComment = $validated['new_comment'] ?? null;
        unset($validated['guardian_id'], $validated['new_comment']);

        // Update student record
        $student->update($validated);

        // Sync parent relationship if guardian_id provided
        if ($guardianId) {
            $student->parents()->syncWithoutDetaching([
                $guardianId => ['relation' => 'guardian']
            ]);
        }

        // Append new comment to admin_comments
        if ($newComment) {
            $existingComments = $student->admin_comments ? $student->admin_comments . "\n" : '';
            $student->update([
                'admin_comments' => $existingComments . '[' . now()->format('M d, Y') . '] ' . $newComment,
            ]);
        }

        return redirect()->route('admin.students.index')->with('success', 'Student updated successfully!');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Student deleted successfully!');
    }

    /**
     * Generate ID numbers for students who don't have one yet.
     */
    public function generateIds()
    {
        // Find all students who don't have a user account with id_number
        $students = \App\Models\Student::whereDoesntHave('user', function($q) {
            $q->whereNotNull('id_number');
        })->orWhereNull('user_id')->get();
        
        $generated = 0;
        $year = $this->getAyStartYear($this->getCurrentAcademicYear());

        foreach ($students as $student) {
            // Generate ID number using the current academic year
            $lastUser = \App\Models\User::where('id_number', 'LIKE', "STD-{$year}-%")
                ->orderBy('id_number', 'desc')->first();
            $nextNum = 1;
            if ($lastUser && $lastUser->id_number) {
                $parts = explode('-', $lastUser->id_number);
                $nextNum = (int)end($parts) + 1;
            }
            $idNumber = "STD-{$year}-" . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
            
            // Default password from DOB or random
            $defaultPassword = $student->date_of_birth 
                ? str_replace('-', '', $student->date_of_birth) 
                : 'Student@' . rand(1000, 9999);
            
            if ($student->user_id) {
                // Update existing user
                $user = \App\Models\User::find($student->user_id);
                if ($user && empty($user->id_number)) {
                    $user->update([
                        'id_number' => $idNumber,
                        'role' => 'student',
                        'is_active' => true,
                    ]);
                }
            } else {
                // Create new user account
                $user = \App\Models\User::create([
                    'name' => $student->full_name,
                    'email' => $student->email ?? $idNumber . '@redemption.edu',
                    'id_number' => $idNumber,
                    'password' => bcrypt($defaultPassword),
                    'role' => 'student',
                    'is_active' => true,
                ]);
                $student->update(['user_id' => $user->id]);
            }
            $generated++;
        }
        
        return redirect()->route('admin.students.index')
            ->with('success', "Generated ID numbers for {$generated} students.");
    }

    /**
     * Get the current academic year from the system.
     * Falls back to the latest academic year, then to the calendar year.
     */
    private function getCurrentAcademicYear(): ?AcademicYear
    {
        return AcademicYear::where('is_current', true)->first()
            ?? AcademicYear::orderBy('id', 'desc')->first();
    }

    /**
     * Extract the start year from an AcademicYear name.
     * e.g. "2024-2025" → "2024", "2025-2026" → "2025"
     */
    private function getAyStartYear(?AcademicYear $ay): string
    {
        if ($ay && $ay->name) {
            $parts = explode('-', $ay->name);
            return $parts[0];
        }
        return date('Y');
    }

    /**
     * Generate the next admission number using the current academic year.
     */
    private function generateAdmissionNumber(): string
    {
        $year = $this->getAyStartYear($this->getCurrentAcademicYear());
        $lastAdmission = Student::where('admission_number', 'like', $year.'-%')
            ->selectRaw("CAST(SUBSTRING(admission_number, -4) AS UNSIGNED) as num")
            ->orderByRaw('num DESC')
            ->first();
        $nextNum = $lastAdmission ? (((int) $lastAdmission->num) + 1) : 1;
        return $year.'-'.str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate the next roll number for a section.
     */
    private function generateRollNumber(int $sectionId): int
    {
        $maxRoll = Student::where('section_id', $sectionId)
            ->selectRaw("CAST(roll_number AS UNSIGNED) as rn")
            ->orderByRaw('rn DESC')
            ->first();
        return $maxRoll ? (((int) $maxRoll->rn) + 1) : 1;
    }

    /**
     * Show inactive/transferred students who can be readmitted.
     */
    public function inactive(Request $request)
    {
        $query = Student::with(['classroom', 'section', 'branch'])
            ->whereIn('status', ['inactive', 'transferred']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                    ->orWhere('admission_number', 'LIKE', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(20);
        $totalInactive = Student::where('status', 'inactive')->count();
        $totalTransferred = Student::where('status', 'transferred')->count();
        $canBeReadmitted = Student::whereIn('status', ['inactive', 'transferred'])->count();

        return view('admin.Student.inactive', compact('students', 'totalInactive', 'totalTransferred', 'canBeReadmitted'));
    }

    /**
     * Show the readmission form for a student.
     */
    public function readmit(Student $student)
    {
        if (!$student->canBeReadmitted()) {
            return redirect()->route('admin.students.inactive')
                ->with('error', 'This student cannot be readmitted. They are currently active.');
        }

        $student->load(['classroom', 'section', 'branch']);
        $classrooms = Classroom::with('sections', 'branch')->get();
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        if ($academicYears->isEmpty()) {
            AcademicYear::create(['name' => '2024-2025']);
            AcademicYear::create(['name' => '2025-2026']);
            $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        }

        return view('admin.Student.readmit', compact('student', 'classrooms', 'academicYears'));
    }

    /**
     * Process student readmission.
     */
    public function readmitStore(Request $request, Student $student)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'admission_date' => 'nullable|date',
            'readmission_remarks' => 'nullable|string|max:1000',
        ]);

        $section = Section::find($validated['section_id']);
        $academicYearId = $validated['academic_year_id'];
        $admissionDate = $validated['admission_date'] ?? now()->toDateString();
        $remarks = $validated['readmission_remarks'] ?? null;

        try {
            $promotionService = new PromotionService();
            $student = $promotionService->readmitStudent(
                $student->id,
                $section->class_id,
                $section->id,
                $academicYearId,
                auth()->id(),
                $remarks
            );

            return redirect()->route('admin.students.show', $student)
                ->with('success', 'Student readmitted successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Readmission failed: ' . $e->getMessage());
        }
    }

    /**
     * Mark a student as left/inactive.
     */
    public function markAsLeft(Request $request, Student $student)
    {
        $validated = $request->validate([
            'leave_reason' => 'required|string|max:500',
            'leave_date' => 'nullable|date',
        ]);

        if ($student->status !== 'active') {
            return back()->with('error', 'Only active students can be marked as left.');
        }

        $student->update([
            'status' => 'inactive',
            'leave_date' => $validated['leave_date'] ?? now()->toDateString(),
            'leave_reason' => $validated['leave_reason'],
            'previous_class_id' => $student->class_id,
            'previous_section_id' => $student->section_id,
        ]);

        // Also deactivate user account
        if ($student->user) {
            $student->user->update(['is_active' => false]);
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Student has been marked as left the school.');
    }

    /**
     * API: Preview next admission number.
     */
    public function apiAdmissionPreview()
    {
        return response()->json([
            'admission_number' => $this->generateAdmissionNumber(),
        ]);
    }

    /**
     * API: Preview next roll number for a section.
     */
    public function apiRollPreview(Request $request)
    {
        $request->validate(['section_id' => 'required|exists:sections,id']);
        return response()->json([
            'roll_number' => $this->generateRollNumber($request->section_id),
        ]);
    }
}
