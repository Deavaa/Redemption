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
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $statusFilter = $request->get('status', '');

        $query = Student::with(['classroom', 'section', 'parents']);

        // Branch scoping: branch_principal only sees students in their branch
        $branchScope = $request->attributes->get('branch_scope');
        if ($branchScope) {
            $query->where('branch_id', $branchScope);
        }

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

        $students = $query->orderBy('full_name')->paginate(10)->withQueryString();

        $baseQuery = Student::query();
        if ($branchScope) {
            $baseQuery->where('branch_id', $branchScope);
        }
        $totalStudents = (clone $baseQuery)->count();
        $activeStudents = (clone $baseQuery)->where('status', 'active')->count();
        $inactiveStudents = (clone $baseQuery)->where('status', 'inactive')->count();

        return view('admin.Student.index', compact('students', 'totalStudents', 'activeStudents', 'inactiveStudents', 'search', 'statusFilter'));
    }

    public function create()
    {
        $classrooms = Classroom::with('sections', 'branch')->get();
        $parents = ParentModel::orderBy('father_name')->get(['id', \DB::raw('father_name as name'), \DB::raw('father_phone as phone')]);
        
        $user = auth()->user();
        $branchScope = request()->attributes->get('branch_scope');
        
        // Branch principal: only see their branch, auto-select it
        if ($branchScope) {
            $branches = Branch::where('id', $branchScope)->get();
            $classrooms = Classroom::with('sections', 'branch')->where('branch_id', $branchScope)->get();
        } else {
            $branches = Branch::all();
        }
        
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
            'roll_number' => 'nullable|string|max:50|unique:students,roll_number',
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

        // Normalize phone numbers to Ethiopian local format (0XXXXXXXXX)
        if (!empty($validated['phone'])) {
            $validated['phone'] = $this->normalizePhone($validated['phone']);
        }
        if (!empty($validated['guardian_phone'])) {
            $validated['guardian_phone'] = $this->normalizePhone($validated['guardian_phone']);
        }

        // Get class_id from section_id
        $section = Section::find($validated['section_id']);
        $validated['class_id'] = $section->class_id;

        // Generate admission_number if not provided
        if (empty($validated['admission_number'])) {
            $validated['admission_number'] = $this->generateAdmissionNumber();
        }

        // Generate roll_number if not provided (format: G{grade}{section}-{NN})
        if (empty($validated['roll_number'])) {
            $validated['roll_number'] = $this->generateRollNumber($validated['section_id']);
        }

        // Ensure roll_number is unique — if collision, regenerate with offset
        $rollAttempts = 0;
        while (Student::where('roll_number', $validated['roll_number'])->exists()) {
            $rollAttempts++;
            if ($rollAttempts > 10) {
                // Fallback: append a unique random suffix to guarantee uniqueness
                $validated['roll_number'] = $validated['roll_number'] . '-' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
                break;
            }
            // Re-generate using the fixed method
            $validated['roll_number'] = $this->generateRollNumber($validated['section_id']);
        }

        // Ensure admission_number is unique
        $admAttempts = 0;
        while (Student::where('admission_number', $validated['admission_number'])->exists()) {
            $admAttempts++;
            $validated['admission_number'] = $this->generateAdmissionNumber();
            if ($admAttempts > 5) break;
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

        // Use try/catch for the final insert to handle any remaining unique constraint violations
        $maxRetries = 3;
        $retryCount = 0;
        while (true) {
            try {
                $student = Student::create($validated);
                break; // Success — exit loop
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $retryCount++;
                if ($retryCount >= $maxRetries) {
                    // Last resort: generate a guaranteed-unique roll_number with timestamp
                    $section = Section::find($validated['section_id']);
                    $class = $section ? ClassRoom::find($section->class_id) : null;
                    $gradeNum = 0;
                    if ($class) {
                        $gradeNum = $class->numeric_name ? (int) $class->numeric_name : (preg_match('/(\d+)/', $class->name, $m) ? (int) $m[1] : 0);
                    }
                    $sectionLetter = $section && preg_match('/([A-Z])$/i', $section->name, $m) ? strtoupper($m[1]) : 'A';
                    $validated['roll_number'] = 'G' . $gradeNum . $sectionLetter . '-' . str_pad(Student::where('roll_number', 'LIKE', 'G' . $gradeNum . $sectionLetter . '-%')->count() + 1, 2, '0', STR_PAD_LEFT) . '-' . now()->format('His');
                    $validated['admission_number'] = $this->generateAdmissionNumber();
                    $student = Student::create($validated);
                    break;
                }
                // If roll_number collision, regenerate and retry
                $validated['roll_number'] = $this->generateRollNumber($validated['section_id']);
                $validated['admission_number'] = $this->generateAdmissionNumber();
            }
        }

        // Auto-create enrollment record for this student
        StudentEnrollment::create([
            'student_id' => $student->id,
            'academic_year_id' => $validated['academic_year_id'],
            'branch_id' => $validated['branch_id'],
            'class_id' => $validated['class_id'],
            'section_id' => $validated['section_id'],
            'roll_number' => $validated['roll_number'] ?? $student->roll_number,
            'enrollment_date' => $validated['admission_date'] ?? now()->toDateString(),
            'status' => 'enrolled',
            'enrollment_type' => 'new',
            'registration_fee' => 0,
            'registration_fee_paid' => 0,
            'registration_fee_status' => 'waived',
            'enrolled_by' => auth()->id(),
        ]);

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
            'roll_number' => 'nullable|string|max:50|unique:students,roll_number,'.$student->id,
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

        $page = $request->input('page', 1);
        return redirect()->route('admin.students.index', ['page' => $page])->with('success', 'Student updated successfully!');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        $page = request()->input('page', 1);
        return redirect()->route('admin.students.index', ['page' => $page])->with('success', 'Student deleted successfully!');
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
     * Generate the next admission number.
     *
     * Format: SOR/{year}/{NNNN}  e.g. SOR/2025/1001
     * This matches the format used by StudentDataSeeder and SchoolDataSeeder.
     */
    private function generateAdmissionNumber(): string
    {
        $year = $this->getAyStartYear($this->getCurrentAcademicYear());
        $prefix = 'SOR/' . $year . '/';

        // Find the max existing admission number with this prefix
        $lastAdmission = Student::where('admission_number', 'LIKE', $prefix . '%')
            ->selectRaw("CAST(SUBSTRING(admission_number, -4) AS UNSIGNED) as num")
            ->orderByRaw('num DESC')
            ->first();

        $nextNum = $lastAdmission ? (((int) $lastAdmission->num) + 1) : 1;
        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate the next roll number for a section.
     *
     * Format: G{grade}{section_letter}-{NN}  e.g. G1A-01, G5B-12
     * This matches the format used by StudentDataSeeder and SchoolDataSeeder.
     *
     * Uses a robust method to extract the numeric suffix regardless of digit count,
     * and keeps retrying until a truly unique roll number is found.
     */
    private function generateRollNumber(int $sectionId): string
    {
        $section = Section::find($sectionId);
        if (!$section) {
            return 'G0A-01';
        }

        // Determine grade number from the class
        $class = ClassRoom::find($section->class_id);
        $gradeNum = 0;
        if ($class) {
            if ($class->numeric_name) {
                $gradeNum = (int) $class->numeric_name;
            } elseif (preg_match('/(\d+)/', $class->name, $m)) {
                $gradeNum = (int) $m[1];
            }
        }

        // Determine section letter from the section name (e.g. "Section A" → "A")
        $sectionLetter = 'A';
        if (preg_match('/([A-Z])$/i', $section->name, $m)) {
            $sectionLetter = strtoupper($m[1]);
        }

        $prefix = 'G' . $gradeNum . $sectionLetter;

        // Find the max existing roll number with this prefix
        // Use LENGTH(prefix)+2 to extract everything after "G{n}{L}-" (handles any digit count)
        $prefixLen = strlen($prefix) + 1; // +1 for the dash
        $maxRoll = Student::where('roll_number', 'LIKE', $prefix . '-%')
            ->selectRaw("CAST(SUBSTRING(roll_number, " . ($prefixLen + 1) . ") AS UNSIGNED) as rn")
            ->orderByRaw('rn DESC')
            ->first();

        $nextNum = $maxRoll ? (((int) $maxRoll->rn) + 1) : 1;

        return $prefix . '-' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Normalize a phone number to Ethiopian local format: 0900000000
     * - Removes spaces, dashes, parentheses, dots
     * - Strips country code prefix (+251, 00251, 251) and prepends 0
     * - Returns the original cleaned string if it doesn't look like a phone number
     */
    private function normalizePhone(string $input): string
    {
        $cleaned = preg_replace('/[\s\-().]/', '', $input);
        if (preg_match('/^(\+251|00251)(\d{9})$/', $cleaned, $m)) {
            return '0' . $m[2];
        }
        if (preg_match('/^251(\d{9})$/', $cleaned, $m)) {
            return '0' . $m[1];
        }
        if (preg_match('/^0\d{9}$/', $cleaned)) {
            return $cleaned;
        }
        return $cleaned;
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
     * Show the transfer form for a student (move to another branch).
     */
    public function transferForm(Student $student)
    {
        $student->load(['classroom', 'section', 'branch']);
        $branches = Branch::where('id', '!=', $student->branch_id)->where('is_active', true)->get();
        $classrooms = Classroom::with('sections')->orderBy('name')->get();

        return view('admin.Student.transfer', compact('student', 'branches', 'classrooms'));
    }

    /**
     * Transfer a student to another branch.
     */
    public function transfer(Request $request, Student $student)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id|different:' . $student->branch_id,
            'section_id' => 'required|exists:sections,id',
            'transfer_reason' => 'nullable|string|max:500',
        ]);

        $section = Section::find($validated['section_id']);
        $newBranchId = $validated['branch_id'];
        $oldBranch = $student->branch ? $student->branch->name : 'Unknown';
        $newBranch = Branch::find($newBranchId)->name;

        // Update the student's branch, class, and section
        $student->update([
            'branch_id' => $newBranchId,
            'class_id' => $section->class_id,
            'section_id' => $validated['section_id'],
            'status' => 'transferred',
            'previous_branch_id' => $student->branch_id,
            'previous_class_id' => $student->class_id,
            'previous_section_id' => $student->section_id,
        ]);

        // Create an enrollment record for the transfer
        $activeAy = AcademicYear::where('is_current', true)->first() ?? AcademicYear::orderBy('id', 'desc')->first();
        if ($activeAy) {
            StudentEnrollment::create([
                'student_id' => $student->id,
                'academic_year_id' => $activeAy->id,
                'branch_id' => $newBranchId,
                'class_id' => $section->class_id,
                'section_id' => $validated['section_id'],
                'roll_number' => $student->roll_number,
                'enrollment_date' => now()->toDateString(),
                'status' => 'enrolled',
                'enrollment_type' => 'transfer',
                'registration_fee' => 0,
                'registration_fee_paid' => 0,
                'registration_fee_status' => 'waived',
                'enrolled_by' => auth()->id(),
                'notes' => "Transferred from {$oldBranch} to {$newBranch}. Reason: " . ($validated['transfer_reason'] ?? 'Not specified'),
            ]);
        }

        // Re-activate the student in the new branch
        $student->update(['status' => 'active']);

        return redirect()->route('admin.students.index')
            ->with('success', "Student transferred from {$oldBranch} to {$newBranch} successfully!");
    }

    /**
     * Bulk transfer multiple students to another branch.
     */
    public function bulkTransfer(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'branch_id' => 'required|exists:branches,id',
            'section_id' => 'required|exists:sections,id',
            'transfer_reason' => 'nullable|string|max:500',
        ]);

        $section = Section::find($validated['section_id']);
        $newBranchId = $validated['branch_id'];
        $newBranch = Branch::find($newBranchId)->name;
        $activeAy = AcademicYear::where('is_current', true)->first() ?? AcademicYear::orderBy('id', 'desc')->first();
        $count = 0;

        foreach ($validated['student_ids'] as $studentId) {
            $student = Student::find($studentId);
            if (!$student) continue;

            $oldBranch = $student->branch ? $student->branch->name : 'Unknown';

            $student->update([
                'branch_id' => $newBranchId,
                'class_id' => $section->class_id,
                'section_id' => $validated['section_id'],
                'status' => 'active',
                'previous_branch_id' => $student->branch_id,
                'previous_class_id' => $student->class_id,
                'previous_section_id' => $student->section_id,
            ]);

            if ($activeAy) {
                StudentEnrollment::create([
                    'student_id' => $student->id,
                    'academic_year_id' => $activeAy->id,
                    'branch_id' => $newBranchId,
                    'class_id' => $section->class_id,
                    'section_id' => $validated['section_id'],
                    'roll_number' => $student->roll_number,
                    'enrollment_date' => now()->toDateString(),
                    'status' => 'enrolled',
                    'enrollment_type' => 'transfer',
                    'registration_fee' => 0,
                    'registration_fee_paid' => 0,
                    'registration_fee_status' => 'waived',
                    'enrolled_by' => auth()->id(),
                    'notes' => "Bulk transferred from {$oldBranch} to {$newBranch}. Reason: " . ($validated['transfer_reason'] ?? 'Not specified'),
                ]);
            }

            $count++;
        }

        return redirect()->route('admin.students.index')
            ->with('success', "{$count} student(s) transferred to {$newBranch} successfully!");
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

    /**
     * API: Return sections with their class name for a given branch.
     */
    public function apiSectionsByBranch(Request $request)
    {
        $branchId = $request->input('branch_id');
        if (!$branchId) {
            return response()->json([]);
        }

        $sections = Section::with('classroom')
            ->whereHas('classroom', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->orderBy('name')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'class_name' => $s->classroom ? $s->classroom->name : '',
                ];
            });

        return response()->json($sections);
    }
}
