<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\ClassRoom;
use App\Models\ParentModel;
use App\Models\Section;
use App\Models\Student;
use App\Services\PromotionService;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// PhpSpreadsheet is optional — used for XLSX support when available.
// Without it (e.g. when ext-gd is missing on XAMPP), CSV format is used instead.
// To enable XLSX support:
//   1. Open C:\xampp\php\php.ini
//   2. Uncomment ;extension=gd  →  extension=gd
//   3. Restart Apache
//   4. Run: composer require phpoffice/phpspreadsheet:^2.0
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $statusFilter = $request->get('status', '');
        $branchFilter = $request->get('branch_id', '');
        $classFilter = $request->get('class_id', '');
        $sectionFilter = $request->get('section_id', '');

        $query = Student::with(['classroom', 'section', 'branch', 'parents']);

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
                    ->orWhere('phone', 'LIKE', "%{$search}%")
                    ->orWhere('id_number', 'LIKE', "%{$search}%")
                    ->orWhere('father_name', 'LIKE', "%{$search}%")
                    ->orWhere('mother_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('gender', 'LIKE', "%{$search}%")
                    ->orWhereHas('classroom', function ($cq) use ($search) {
                        $cq->where('name', 'LIKE', "%{$search}%")
                          ->orWhere('numeric_name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('section', function ($sq) use ($search) {
                        $sq->where('name', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('branch', function ($bq) use ($search) {
                        $bq->where('name', 'LIKE', "%{$search}%");
                    });
            });
        }

        if ($statusFilter && in_array($statusFilter, ['active', 'inactive', 'transferred', 'graduated'])) {
            $query->where('status', $statusFilter);
        } else {
            // Default: show ALL statuses (not just active) so search finds everyone
            // Only filter by status when explicitly selected
        }

        // Filter by branch
        if ($branchFilter) {
            $query->where('branch_id', $branchFilter);
        }

        // Filter by class
        if ($classFilter) {
            $query->where('class_id', $classFilter);
        }

        // Filter by section
        if ($sectionFilter) {
            $query->where('section_id', $sectionFilter);
        }

        $students = $query->orderBy('full_name')->paginate(10)->withQueryString();

        // Stats base query (respects branch scope)
        $baseQuery = Student::query();
        if ($branchScope) {
            $baseQuery->where('branch_id', $branchScope);
        }
        // Also apply same filters for stats so they reflect filtered data
        $statsQuery = clone $baseQuery;
        if ($branchFilter) $statsQuery->where('branch_id', $branchFilter);
        if ($classFilter) $statsQuery->where('class_id', $classFilter);
        if ($sectionFilter) $statsQuery->where('section_id', $sectionFilter);

        $totalStudents = (clone $statsQuery)->count();
        $activeStudents = (clone $statsQuery)->where('status', 'active')->count();
        $inactiveStudents = (clone $statsQuery)->where('status', 'inactive')->count();

        // Load filter dropdown data
        $branches = $branchScope ? Branch::where('id', $branchScope)->get() : Branch::orderBy('name')->get();
        $classes = ClassRoom::when($branchScope, fn($q) => $q->where('branch_id', $branchScope))->orderBy('numeric_name')->orderBy('name')->get();
        $sections = collect();
        if ($classFilter) {
            $sections = Section::where('class_id', $classFilter)
                ->when($branchScope, fn($q) => $q->whereHas('classRoom', fn($cq) => $cq->where('branch_id', $branchScope)))
                ->orderBy('name')->get();
        }

        return view('admin.Student.index', compact(
            'students', 'totalStudents', 'activeStudents', 'inactiveStudents',
            'search', 'statusFilter', 'branchFilter', 'classFilter', 'sectionFilter',
            'branches', 'classes', 'sections'
        ));
    }

    public function create()
    {
        $parents = ParentModel::orderBy('father_name')->get(['id', \DB::raw('father_name as name'), \DB::raw('father_phone as phone')]);
        
        $user = auth()->user();
        $branchScope = request()->attributes->get('branch_scope');
        
        // Branch principal: only see their branch, auto-select it
        if ($branchScope) {
            $branches = Branch::where('id', $branchScope)->get();
        } else {
            $branches = Branch::all();
        }

        // Load classrooms with AY-aware fallback
        $classrooms = $this->loadClassroomsWithFallback($branchScope);
        
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
            // Mid-year admission fields
            'joined_term' => 'nullable|integer|in:1,2',
            'first_term_mark_override' => 'nullable|numeric',
            'first_term_rank_override' => 'nullable|integer',
            'special_needs' => 'nullable|boolean',
        ]);

        // Ensure mid-year fields are set (default if not provided)
        $validated['joined_term'] = $validated['joined_term'] ?? 1;
        if ((int)$validated['joined_term'] === 1) {
            $validated['first_term_mark_override'] = null;
            $validated['first_term_rank_override'] = null;
        }
        $validated['special_needs'] = (bool)($validated['special_needs'] ?? false);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('student-photos', 'public');
            $validated['photo'] = $photoPath;
        }

        // Auto-assign branch for branch principal
        if (auth()->user()->role === 'branch_principal' && auth()->user()->branch_id) {
            $validated['branch_id'] = auth()->user()->branch_id;
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
        // Default password for all users is '123456'
        $defaultPassword = '123456';

        $user = \App\Models\User::create([
            'name' => $validated['full_name'],
            'email' => $validated['email'] ?? $idNumber . '@redemption.edu',
            'id_number' => $idNumber,
            'password' => bcrypt($defaultPassword),
            'must_change_password' => true,
            'role' => 'student',
            'is_active' => true,
            'phone' => $validated['phone'] ?? null,
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

        // Notify relevant users about the new enrollment
        try {
            \App\Services\AlertService::notifyStudentEnrolled(
                $validated['branch_id'],
                $validated['full_name'],
                $validated['academic_year_id'] ?? null
            );
        } catch (\Exception $e) {
            \Log::warning('Enrollment notification failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.students.index')->with('success', 'Student enrolled successfully!');
    }

    public function show(Student $student)
    {
        $student->load(['classroom', 'section', 'branch', 'academicYear', 'parents', 'currentEnrollment.branch', 'currentEnrollment.classroom', 'currentEnrollment.section', 'currentEnrollment.academicYear']);
        return view('admin.Student.show', ['data' => $student]);
    }

    public function edit(Student $student)
    {
        $branchScope = request()->attributes->get('branch_scope');
        $classrooms = $this->loadClassroomsWithFallback($branchScope);
        $parents = ParentModel::orderBy('father_name')->get(['id', \DB::raw('father_name as name'), \DB::raw('father_phone as phone')]);
        $branches = $branchScope ? Branch::where('id', $branchScope)->get() : Branch::all();
        $academicYears = AcademicYear::all();
        $sections = Section::when($branchScope, fn($q) => $q->whereHas('classRoom', fn($cq) => $cq->where('branch_id', $branchScope)))->get();
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
            // Mid-year admission fields
            'joined_term' => 'nullable|integer|in:1,2',
            'first_term_mark_override' => 'nullable|numeric',
            'first_term_rank_override' => 'nullable|integer',
            'special_needs' => 'nullable|boolean',
        ]);

        // Ensure mid-year fields are set (default if not provided)
        $validated['joined_term'] = $validated['joined_term'] ?? 1;
        if ((int)$validated['joined_term'] === 1) {
            $validated['first_term_mark_override'] = null;
            $validated['first_term_rank_override'] = null;
        }
        $validated['special_needs'] = (bool)($validated['special_needs'] ?? false);

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

        // Ensure mid-year fields are set (default if not provided)
        $validated['joined_term'] = $validated['joined_term'] ?? 1;
        // If joined_term is 1 (regular), clear the override fields
        if ((int)$validated['joined_term'] === 1) {
            $validated['first_term_mark_override'] = null;
            $validated['first_term_rank_override'] = null;
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
            
            // Default password for all users is '123456'
            $defaultPassword = '123456';
            
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
                    'must_change_password' => true,
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
     * Load classrooms with academic year awareness and fallback.
     * First tries the current academic year, then falls back to all classes for the branch.
     */
    private function loadClassroomsWithFallback(?int $branchId)
    {
        $currentAy = $this->getCurrentAcademicYear();

        $query = ClassRoom::with('sections', 'branch');
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        if ($currentAy) {
            $query->where('academic_year_id', $currentAy->id);
        }
        $classrooms = $query->orderBy('numeric_name')->orderBy('name')->get();

        // Fallback: if no classes for current AY, show all for the branch
        if ($classrooms->isEmpty()) {
            $fallbackQuery = ClassRoom::with('sections', 'branch');
            if ($branchId) {
                $fallbackQuery->where('branch_id', $branchId);
            }
            $classrooms = $fallbackQuery->orderBy('numeric_name')->orderBy('name')->get();
        }

        return $classrooms;
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
        $branchScope = $request->attributes->get('branch_scope');

        $query = Student::with(['classroom', 'section', 'branch'])
            ->whereIn('status', ['inactive', 'transferred']);

        if ($branchScope) {
            $query->where('branch_id', $branchScope);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                    ->orWhere('admission_number', 'LIKE', "%{$search}%");
            });
        }

        $students = $query->latest()->paginate(20);

        $inactiveBaseQuery = Student::whereIn('status', ['inactive', 'transferred']);
        if ($branchScope) $inactiveBaseQuery->where('branch_id', $branchScope);

        $totalInactive = (clone $inactiveBaseQuery)->where('status', 'inactive')->count();
        $totalTransferred = (clone $inactiveBaseQuery)->where('status', 'transferred')->count();
        $canBeReadmitted = $inactiveBaseQuery->count();

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
        $branchScope = request()->attributes->get('branch_scope');
        $classrooms = $this->loadClassroomsWithFallback($branchScope);
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
        $classrooms = $this->loadClassroomsWithFallback(null);

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

        // Notify relevant users about the transfer
        try {
            \App\Services\AlertService::notifyStudentTransfer(
                $student->previous_branch_id ?? $validated['branch_id'],
                $newBranchId,
                $student->full_name
            );
        } catch (\Exception $e) {
            \Log::warning('Transfer notification failed: ' . $e->getMessage());
        }

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
     * API: Return sections for a given class.
     */
    public function getSections($classId)
    {
        return response()->json(
            Section::where('class_id', $classId)->orderBy('name')->get(['id', 'name'])
        );
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
            ->join('classes', 'sections.class_id', '=', 'classes.id')
            ->where('classes.branch_id', $branchId)
            ->orderBy('classes.numeric_name')
            ->orderBy('classes.name')
            ->orderBy('sections.name')
            ->select('sections.*')
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

    /**
     * API: Return classes for a given branch (for filter dropdowns).
     */
    public function apiClassesByBranch(Request $request)
    {
        $branchId = $request->input('branch_id');
        if (!$branchId) {
            return response()->json([]);
        }

        return response()->json(
            ClassRoom::where('branch_id', $branchId)
                ->orderBy('numeric_name')
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    /**
     * Show the bulk student creation form.
     * Allows adding multiple students at once with shared branch/class/section/academic year.
     */
    public function bulkCreate()
    {
        $user = auth()->user();
        $branchScope = request()->attributes->get('branch_scope');

        if ($branchScope) {
            $branches = Branch::where('id', $branchScope)->get();
        } else {
            $branches = Branch::all();
        }

        $classrooms = $this->loadClassroomsWithFallback($branchScope);

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        if ($academicYears->isEmpty()) {
            AcademicYear::create(['name' => '2024-2025']);
            AcademicYear::create(['name' => '2025-2026']);
            $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        }

        return view('admin.Student.bulk-create', compact('branches', 'classrooms', 'academicYears'));
    }

    /**
     * Process bulk student creation.
     * Creates multiple students with shared enrollment settings.
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'admission_date' => 'nullable|date',
            'students' => 'required|array|min:1',
            'students.*.full_name' => 'required|string|max:255',
            'students.*.gender' => 'nullable|in:male,female,other',
            'students.*.phone' => 'nullable|string|max:20',
            'students.*.guardian_name' => 'nullable|string|max:255',
            'students.*.guardian_phone' => 'nullable|string|max:20',
            'students.*.date_of_birth' => 'nullable|date',
        ]);

        // Auto-assign branch for branch principal
        if (auth()->user()->role === 'branch_principal' && auth()->user()->branch_id) {
            $validated['branch_id'] = auth()->user()->branch_id;
        }

        $section = Section::find($validated['section_id']);
        $classId = $section->class_id;
        $admissionDate = $validated['admission_date'] ?? now()->toDateString();
        $year = $this->getAyStartYear($this->getCurrentAcademicYear());

        $created = 0;
        $skipped = 0;
        $errors = [];       // Validation errors
        $duplicates = [];   // Duplicate entry errors
        $systemErrors = []; // Unexpected system errors

        DB::beginTransaction();
        try {
            foreach ($validated['students'] as $index => $studentData) {
                $rowLabel = "Row " . ($index + 1);

                // Skip empty rows
                if (empty(trim($studentData['full_name'] ?? ''))) {
                    $skipped++;
                    continue;
                }

                // Validate phone format
                if (!empty($studentData['phone']) && !preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $studentData['phone'])) {
                    $errors[] = "{$rowLabel} ({$studentData['full_name']}): Invalid phone number '{$studentData['phone']}'. Use digits, spaces, +, -. Skipped.";
                    continue;
                }

                // Validate guardian phone format
                if (!empty($studentData['guardian_phone']) && !preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $studentData['guardian_phone'])) {
                    $errors[] = "{$rowLabel} ({$studentData['full_name']}): Invalid guardian phone '{$studentData['guardian_phone']}'. Use digits, spaces, +, -. Skipped.";
                    continue;
                }

                // Generate admission number
                $admissionNumber = $this->generateAdmissionNumber();

                // Generate roll number
                $rollNumber = $this->generateRollNumber($validated['section_id']);

                // Ensure uniqueness
                $rollAttempts = 0;
                while (Student::where('roll_number', $rollNumber)->exists()) {
                    $rollAttempts++;
                    $rollNumber = $this->generateRollNumber($validated['section_id']);
                    if ($rollAttempts > 10) {
                        $rollNumber = $rollNumber . '-' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
                        break;
                    }
                }

                // Generate student ID number
                $lastUser = \App\Models\User::where('id_number', 'LIKE', "STD-{$year}-%")
                    ->orderBy('id_number', 'desc')->first();
                $nextNum = 1;
                if ($lastUser && $lastUser->id_number) {
                    $parts = explode('-', $lastUser->id_number);
                    $nextNum = (int)end($parts) + 1;
                }
                $idNumber = "STD-{$year}-" . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

                // Normalize phone
                $phone = !empty($studentData['phone']) ? $this->normalizePhone($studentData['phone']) : null;
                $guardianPhone = !empty($studentData['guardian_phone']) ? $this->normalizePhone($studentData['guardian_phone']) : null;

                // Create user account
                try {
                    $user = \App\Models\User::create([
                        'name' => $studentData['full_name'],
                        'email' => $idNumber . '@redemption.edu',
                        'id_number' => $idNumber,
                        'password' => bcrypt('123456'),
                        'must_change_password' => true,
                        'role' => 'student',
                        'is_active' => true,
                        'phone' => $phone,
                    ]);
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    $duplicates[] = "{$rowLabel} ({$studentData['full_name']}): A student with this generated ID already exists. Skipped.";
                    continue;
                } catch (\Exception $e) {
                    $systemErrors[] = "{$rowLabel} ({$studentData['full_name']}): Failed to create user account - " . $e->getMessage();
                    continue;
                }

                // Create student record
                try {
                    $student = Student::create([
                        'user_id' => $user->id,
                        'full_name' => $studentData['full_name'],
                        'gender' => $studentData['gender'] ?? null,
                        'phone' => $phone,
                        'guardian_name' => $studentData['guardian_name'] ?? null,
                        'guardian_phone' => $guardianPhone,
                        'date_of_birth' => $studentData['date_of_birth'] ?? null,
                        'branch_id' => $validated['branch_id'],
                        'class_id' => $classId,
                        'section_id' => $validated['section_id'],
                        'academic_year_id' => $validated['academic_year_id'],
                        'admission_number' => $admissionNumber,
                        'roll_number' => $rollNumber,
                        'admission_date' => $admissionDate,
                        'status' => 'active',
                    ]);

                    // Create enrollment record
                    StudentEnrollment::create([
                        'student_id' => $student->id,
                        'academic_year_id' => $validated['academic_year_id'],
                        'branch_id' => $validated['branch_id'],
                        'class_id' => $classId,
                        'section_id' => $validated['section_id'],
                        'roll_number' => $rollNumber,
                        'enrollment_date' => $admissionDate,
                        'status' => 'enrolled',
                        'enrollment_type' => 'new',
                        'registration_fee' => 0,
                        'registration_fee_paid' => 0,
                        'registration_fee_status' => 'waived',
                        'enrolled_by' => auth()->id(),
                    ]);

                    $created++;
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    $duplicates[] = "{$rowLabel} ({$studentData['full_name']}): Duplicate admission/roll number - skipped.";
                    $user->delete(); // Clean up the created user
                    continue;
                } catch (\Exception $e) {
                    $systemErrors[] = "{$rowLabel} ({$studentData['full_name']}): Failed to create student record - " . $e->getMessage();
                    // Clean up the created user to avoid orphans
                    try { $user->delete(); } catch (\Exception $_) {}
                    continue;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.students.bulk-create')
                ->with('error', 'Bulk enrollment failed completely. Error: ' . $e->getMessage());
        }

        // Notify about bulk enrollment
        if ($created > 0) {
            try {
                \App\Services\AlertService::notifyStudentEnrolled(
                    $validated['branch_id'],
                    "{$created} new students",
                    $validated['academic_year_id']
                );
            } catch (\Exception $e) {
                \Log::warning('Bulk enrollment notification failed: ' . $e->getMessage());
            }
        }

        // Build categorized error details for display
        $errorDetails = [];
        $totalFailed = count($errors) + count($duplicates) + count($systemErrors);

        if (!empty($errors)) {
            $errorDetails[] = ['type' => 'validation', 'label' => 'Validation Errors (' . count($errors) . ')', 'items' => $errors];
        }
        if (!empty($duplicates)) {
            $errorDetails[] = ['type' => 'duplicate', 'label' => 'Duplicate Entries (' . count($duplicates) . ')', 'items' => $duplicates];
        }
        if (!empty($systemErrors)) {
            $errorDetails[] = ['type' => 'system', 'label' => 'System Errors (' . count($systemErrors) . ')', 'items' => $systemErrors];
        }

        // Determine the appropriate flash message type
        if ($created === 0 && $totalFailed > 0) {
            // Complete failure — no students were created
            $redirect = redirect()->route('admin.students.bulk-create')
                ->with('error', "No students were enrolled. {$totalFailed} row(s) had problems. Please fix the errors and try again.")
                ->with('bulk_error_details', $errorDetails);
        } elseif ($totalFailed > 0) {
            // Partial success — some created, some failed
            $redirect = redirect()->route('admin.students.bulk-create')
                ->with('warning', "Partially completed: {$created} student(s) enrolled successfully, but {$totalFailed} row(s) had problems.")
                ->with('bulk_error_details', $errorDetails);
        } else {
            // Complete success
            $message = "Successfully enrolled {$created} student(s).";
            if ($skipped > 0) {
                $message .= " Skipped {$skipped} empty row(s).";
            }
            $redirect = redirect()->route('admin.students.index')
                ->with('success', $message);
        }

        return $redirect;
    }

    /**
     * Download an empty Excel template for bulk student upload.
     * Includes column headers, data validation for Gender, and an example row.
     */
    public function downloadTemplate()
    {
        // Check if PhpSpreadsheet is available (requires ext-gd for v2+)
        // If not available, fall back to CSV format which needs no special extensions.
        if (class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            return $this->downloadTemplateXlsx();
        }

        return $this->downloadTemplateCsv();
    }

    /**
     * Download a CSV template (always works — no special PHP extensions required).
     * Used as fallback when PhpSpreadsheet/ext-gd is not available.
     */
    private function downloadTemplateCsv()
    {
        $headers = ['Full Name (required)', 'Gender (Male/Female)', 'Phone', 'Guardian Name', 'Guardian Phone', 'Date of Birth (YYYY-MM-DD)'];
        $exampleData = ['John Doe', 'Male', '0901234567', 'Jane Doe', '0907654321', '2010-05-15'];

        // Build CSV content
        $csv = '';
        $csv .= $this->csvLine($headers);
        $csv .= $this->csvLine($exampleData);

        $filename = 'student_upload_template.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Download an XLSX template (requires PhpSpreadsheet + ext-gd).
     * Provides rich formatting: styled headers, data validation dropdowns, column widths.
     */
    private function downloadTemplateXlsx()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Student Upload Template');

        // Column headers
        $headers = [
            'Full Name (required)',
            'Gender (Male/Female)',
            'Phone',
            'Guardian Name',
            'Guardian Phone',
            'Date of Birth (YYYY-MM-DD)',
        ];

        // Style for headers
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4361EE']],
            'alignment' => ['horizontal' => 'center', 'wrapText' => true],
            'borders' => [
                'allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CCCCCC']],
            ],
        ];

        // Write headers
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
            $col++;
        }

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(22);

        // Add example data row
        $exampleData = ['John Doe', 'Male', '0901234567', 'Jane Doe', '0907654321', '2010-05-15'];
        $col = 'A';
        foreach ($exampleData as $value) {
            $sheet->setCellValue($col . '2', $value);
            $col++;
        }

        // Style the example row with a light background to indicate it's sample data
        $exampleStyle = [
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFF3CD']],
            'font' => ['italic' => true, 'color' => ['rgb' => '856404']],
        ];
        $sheet->getStyle('A2:F2')->applyFromArray($exampleStyle);

        // Add data validation (dropdown) for Gender column (B2:B1000)
        $validation = new DataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
        $validation->setAllowBlank(true);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Invalid Gender');
        $validation->setError('Please select Male or Female.');
        $validation->setPromptTitle('Gender');
        $validation->setPrompt('Select Male or Female');
        $validation->setFormula1('"Male,Female"');
        $sheet->setDataValidation('B2:B1000', $validation);

        // Add a note in the sheet about format
        $sheet->getComment('A1')->getText()->createTextRun('Student full name is required. All other fields are optional.');
        $sheet->getComment('F1')->getText()->createTextRun('Date format must be YYYY-MM-DD (e.g. 2010-05-15)');

        // Set row height for header
        $sheet->getRowDimension('1')->setRowHeight(30);

        // Write and download
        $writer = new Xlsx($spreadsheet);
        $filename = 'student_upload_template.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Format an array as a CSV line (handles values containing commas/quotes).
     */
    private function csvLine(array $fields): string
    {
        $line = '';
        foreach ($fields as $i => $field) {
            if ($i > 0) {
                $line .= ',';
            }
            // Enclose in quotes if value contains comma, quote, or newline
            if (preg_match('/[,"\r\n]/', $field)) {
                $line .= '"' . str_replace('"', '""', $field) . '"';
            } else {
                $line .= $field;
            }
        }
        $line .= "\n";
        return $line;
    }

    /**
     * Upload a CSV/XLSX file with student data and create students in bulk.
     * Uses CSV parsing (fgetcsv) natively — no special PHP extensions required.
     * Falls back to PhpSpreadsheet for XLSX files if the library is available.
     * Uses the same logic as bulkStore for consistency.
     */
    public function uploadStudents(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:5120',
            'branch_id' => 'required|exists:branches,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'admission_date' => 'nullable|date',
        ]);

        // Auto-assign branch for branch principal
        if (auth()->user()->role === 'branch_principal' && auth()->user()->branch_id) {
            $validated['branch_id'] = auth()->user()->branch_id;
        }

        $file = $request->file('file');
        $filePath = $file->getRealPath();
        $extension = strtolower($file->getClientOriginalExtension());

        // Parse the uploaded file into rows
        $rows = [];
        try {
            if ($extension === 'csv' || $extension === 'txt') {
                // CSV parsing — always works, no special extensions needed
                $rows = $this->parseCsvFile($filePath);
            } elseif (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                // XLSX parsing via PhpSpreadsheet (requires ext-gd)
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray(null, true, true, true);
                // Convert associative keys (A, B, C...) to numeric (0, 1, 2...)
                $rows = array_map(function ($row) {
                    return array_values($row);
                }, $rows);
            } else {
                return back()->with('error', 'XLSX files require the PhpSpreadsheet library and the GD extension. Please upload a CSV file instead, or enable the GD extension in your XAMPP php.ini (uncomment ;extension=gd, then restart Apache) and run: composer require phpoffice/phpspreadsheet:^2.0');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Could not read the uploaded file. Please ensure it is a valid file. Error: ' . $e->getMessage());
        }

        if (count($rows) < 2) {
            return back()->with('error', 'The uploaded file appears to be empty or has no data rows.');
        }

        // Remove header row
        array_shift($rows);

        $section = Section::find($validated['section_id']);
        $classId = $section->class_id;
        $admissionDate = $validated['admission_date'] ?? now()->toDateString();
        $year = $this->getAyStartYear($this->getCurrentAcademicYear());

        $created = 0;
        $skipped = 0;
        $errors = [];       // Validation errors (user can fix and re-upload)
        $duplicates = [];   // Duplicate entry errors
        $systemErrors = []; // Unexpected system errors

        DB::beginTransaction();
        try {
            foreach ($rows as $rowIndex => $row) {
                $excelRow = $rowIndex + 2; // +2 because row index starts at 0 and we removed the header

                // Extract values from numeric-indexed array (columns 0–5)
                $fullName = trim($row[0] ?? '');
                $gender = trim($row[1] ?? '');
                $phone = trim($row[2] ?? '');
                $guardianName = trim($row[3] ?? '');
                $guardianPhone = trim($row[4] ?? '');
                $dateOfBirth = trim($row[5] ?? '');

                // Skip rows with empty Full Name
                if (empty($fullName)) {
                    $skipped++;
                    continue;
                }

                // Validate full name length
                if (strlen($fullName) > 255) {
                    $errors[] = "Row {$excelRow}: Name is too long (max 255 characters). Skipped.";
                    continue;
                }

                // Validate gender value
                $genderLower = strtolower($gender);
                if (!empty($gender) && !in_array($genderLower, ['male', 'female'])) {
                    $errors[] = "Row {$excelRow} ({$fullName}): Invalid gender '{$gender}'. Use Male or Female. Skipped.";
                    continue;
                }
                $genderValue = !empty($gender) ? $genderLower : null;

                // Validate phone format
                if (!empty($phone) && !preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $phone)) {
                    $errors[] = "Row {$excelRow} ({$fullName}): Invalid phone number '{$phone}'. Use digits, spaces, +, -. Skipped.";
                    continue;
                }

                // Validate guardian phone format
                if (!empty($guardianPhone) && !preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $guardianPhone)) {
                    $errors[] = "Row {$excelRow} ({$fullName}): Invalid guardian phone '{$guardianPhone}'. Use digits, spaces, +, -. Skipped.";
                    continue;
                }

                // Validate date of birth format
                if (!empty($dateOfBirth)) {
                    try {
                        $dob = \Carbon\Carbon::parse($dateOfBirth);
                        $dateOfBirth = $dob->toDateString();
                    } catch (\Exception $e) {
                        $errors[] = "Row {$excelRow} ({$fullName}): Invalid date of birth '{$dateOfBirth}'. Use YYYY-MM-DD format. Skipped.";
                        continue;
                    }
                } else {
                    $dateOfBirth = null;
                }

                // Generate admission number
                $admissionNumber = $this->generateAdmissionNumber();

                // Generate roll number
                $rollNumber = $this->generateRollNumber($validated['section_id']);

                // Ensure uniqueness
                $rollAttempts = 0;
                while (Student::where('roll_number', $rollNumber)->exists()) {
                    $rollAttempts++;
                    $rollNumber = $this->generateRollNumber($validated['section_id']);
                    if ($rollAttempts > 10) {
                        $rollNumber = $rollNumber . '-' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT);
                        break;
                    }
                }

                // Generate student ID number
                $lastUser = \App\Models\User::where('id_number', 'LIKE', "STD-{$year}-%")
                    ->orderBy('id_number', 'desc')->first();
                $nextNum = 1;
                if ($lastUser && $lastUser->id_number) {
                    $parts = explode('-', $lastUser->id_number);
                    $nextNum = (int)end($parts) + 1;
                }
                $idNumber = "STD-{$year}-" . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

                // Normalize phone numbers
                $phoneNormalized = !empty($phone) ? $this->normalizePhone($phone) : null;
                $guardianPhoneNormalized = !empty($guardianPhone) ? $this->normalizePhone($guardianPhone) : null;

                // Create user account
                try {
                    $user = \App\Models\User::create([
                        'name' => $fullName,
                        'email' => $idNumber . '@redemption.edu',
                        'id_number' => $idNumber,
                        'must_change_password' => true,
                        'password' => bcrypt('123456'),
                        'role' => 'student',
                        'is_active' => true,
                        'phone' => $phoneNormalized,
                    ]);
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    $duplicates[] = "Row {$excelRow} ({$fullName}): A student with this generated ID already exists. Skipped.";
                    continue;
                } catch (\Exception $e) {
                    $systemErrors[] = "Row {$excelRow} ({$fullName}): Failed to create user account - " . $e->getMessage();
                    continue;
                }

                // Create student record
                try {
                    $student = Student::create([
                        'user_id' => $user->id,
                        'full_name' => $fullName,
                        'gender' => $genderValue,
                        'phone' => $phoneNormalized,
                        'guardian_name' => !empty($guardianName) ? $guardianName : null,
                        'guardian_phone' => $guardianPhoneNormalized,
                        'date_of_birth' => $dateOfBirth,
                        'branch_id' => $validated['branch_id'],
                        'class_id' => $classId,
                        'section_id' => $validated['section_id'],
                        'academic_year_id' => $validated['academic_year_id'],
                        'admission_number' => $admissionNumber,
                        'roll_number' => $rollNumber,
                        'admission_date' => $admissionDate,
                        'status' => 'active',
                    ]);

                    // Create enrollment record
                    StudentEnrollment::create([
                        'student_id' => $student->id,
                        'academic_year_id' => $validated['academic_year_id'],
                        'branch_id' => $validated['branch_id'],
                        'class_id' => $classId,
                        'section_id' => $validated['section_id'],
                        'roll_number' => $rollNumber,
                        'enrollment_date' => $admissionDate,
                        'status' => 'enrolled',
                        'enrollment_type' => 'new',
                        'registration_fee' => 0,
                        'registration_fee_paid' => 0,
                        'registration_fee_status' => 'waived',
                        'enrolled_by' => auth()->id(),
                    ]);

                    $created++;
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    $duplicates[] = "Row {$excelRow} ({$fullName}): Duplicate admission/roll number - skipped.";
                    $user->delete(); // Clean up the created user
                    continue;
                } catch (\Exception $e) {
                    $systemErrors[] = "Row {$excelRow} ({$fullName}): Failed to create student record - " . $e->getMessage();
                    // Clean up the created user to avoid orphans
                    try { $user->delete(); } catch (\Exception $_) {}
                    continue;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.students.bulk-create')
                ->with('error', 'Upload failed completely. The file could not be processed. Error: ' . $e->getMessage());
        }

        // Notify about bulk enrollment
        if ($created > 0) {
            try {
                \App\Services\AlertService::notifyStudentEnrolled(
                    $validated['branch_id'],
                    "{$created} new students (uploaded)",
                    $validated['academic_year_id']
                );
            } catch (\Exception $e) {
                \Log::warning('Upload enrollment notification failed: ' . $e->getMessage());
            }
        }

        // Build categorized error details for display
        $errorDetails = [];
        $totalFailed = count($errors) + count($duplicates) + count($systemErrors);

        if (!empty($errors)) {
            $errorDetails[] = ['type' => 'validation', 'label' => 'Validation Errors (' . count($errors) . ')', 'items' => $errors];
        }
        if (!empty($duplicates)) {
            $errorDetails[] = ['type' => 'duplicate', 'label' => 'Duplicate Entries (' . count($duplicates) . ')', 'items' => $duplicates];
        }
        if (!empty($systemErrors)) {
            $errorDetails[] = ['type' => 'system', 'label' => 'System Errors (' . count($systemErrors) . ')', 'items' => $systemErrors];
        }

        // Determine the appropriate flash message type
        if ($created === 0 && $totalFailed > 0) {
            // Complete failure — no students were created
            $redirect = redirect()->route('admin.students.bulk-create')
                ->with('error', "No students were enrolled. {$totalFailed} row(s) had problems. Please fix the errors and try again.")
                ->with('bulk_error_details', $errorDetails);
        } elseif ($totalFailed > 0) {
            // Partial success — some created, some failed
            $redirect = redirect()->route('admin.students.bulk-create')
                ->with('warning', "Partially completed: {$created} student(s) enrolled successfully, but {$totalFailed} row(s) had problems.")
                ->with('bulk_error_details', $errorDetails);
        } else {
            // Complete success
            $message = "Successfully enrolled {$created} student(s) from file.";
            if ($skipped > 0) {
                $message .= " Skipped {$skipped} empty row(s).";
            }
            $redirect = redirect()->route('admin.students.bulk-create')
                ->with('success', $message);
        }

        return $redirect;
    }

    /**
     * Parse a CSV file into an array of rows (each row is a numeric-indexed array).
     * Uses PHP's built-in fgetcsv() — no special extensions required.
     */
    private function parseCsvFile(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            throw new \RuntimeException('Cannot open CSV file for reading.');
        }

        // Detect BOM for UTF-8 and skip it
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            // Not a BOM — rewind to start
            rewind($handle);
        }

        while (($data = fgetcsv($handle, 0, ',')) !== false) {
            $rows[] = $data;
        }
        fclose($handle);

        return $rows;
    }

    /**
     * Export students as CSV, filtered by academic year / class / section / branch.
     */
    public function exportCsv(Request $request)
    {
        $ayId = $request->query('academic_year_id');
        $classId = $request->query('class_id');
        $sectionId = $request->query('section_id');
        $branchId = $request->query('branch_id');
        $status = $request->query('status', 'active');

        $query = Student::query();
        if ($branchId) $query->where('branch_id', $branchId);
        if ($classId) $query->where('class_id', $classId);
        if ($sectionId) $query->where('section_id', $sectionId);
        if ($status && $status !== 'all') $query->where('status', $status);

        // If academic year filter, use enrollment records
        if ($ayId) {
            $studentIds = StudentEnrollment::where('academic_year_id', $ayId)
                ->where('status', 'enrolled')
                ->pluck('student_id');
            $query->whereIn('id', $studentIds);
        }

        $students = $query->orderBy('full_name')->get();

        $headers = ['id', 'full_name', 'admission_number', 'roll_number', 'gender', 'date_of_birth', 'email', 'phone', 'guardian_name', 'guardian_phone', 'address', 'branch_id', 'class_id', 'section_id', 'academic_year_id', 'admission_date', 'status', 'blood_group', 'religion', 'nationality', 'previous_school', 'ethnicity', 'place_of_birth', 'medical_conditions', 'allergies', 'notes'];

        $filename = 'students_export_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($headers, $students) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM for Excel
            fputcsv($out, $headers);
            foreach ($students as $s) {
                $row = [];
                foreach ($headers as $h) {
                    $row[] = $s->$h ?? '';
                }
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Import students from a CSV file. Updates existing (by id or admission_number) or creates new.
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") fseek($handle, 0);

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return back()->with('error', 'CSV file is empty or invalid.');
        }
        $headers = array_map('trim', $headers);

        $allowedFields = ['full_name', 'admission_number', 'roll_number', 'gender', 'date_of_birth', 'email', 'phone', 'guardian_name', 'guardian_phone', 'address', 'branch_id', 'class_id', 'section_id', 'academic_year_id', 'admission_date', 'status', 'blood_group', 'religion', 'nationality', 'previous_school', 'ethnicity', 'place_of_birth', 'medical_conditions', 'allergies', 'notes'];

        $saved = 0; $updated = 0; $errors = []; $lineNum = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $lineNum++;
            $data = [];
            $id = null; $admNum = null;
            foreach ($headers as $i => $h) {
                $val = isset($row[$i]) ? trim($row[$i]) : '';
                if ($h === 'id') { $id = $val; continue; }
                if ($h === 'admission_number') { $admNum = $val; }
                if (in_array($h, $allowedFields)) {
                    $data[$h] = $val !== '' ? $val : null;
                }
            }
            if (empty($data['full_name'])) {
                $errors[] = "Line $lineNum: missing full_name — skipped.";
                continue;
            }
            try {
                $existing = null;
                if ($id) $existing = Student::find($id);
                if (!$existing && $admNum) $existing = Student::where('admission_number', $admNum)->first();
                if ($existing) {
                    $existing->update(array_filter($data, fn($v) => $v !== null));
                    $updated++;
                } else {
                    Student::create($data);
                    $saved++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Line $lineNum: " . $e->getMessage();
            }
        }
        fclose($handle);

        $msg = "Imported $saved new students, updated $updated existing.";
        if (count($errors) > 0) {
            $msg .= " " . count($errors) . " errors: " . implode(' | ', array_slice($errors, 0, 5));
        }
        return back()->with('success', $msg);
    }
}
