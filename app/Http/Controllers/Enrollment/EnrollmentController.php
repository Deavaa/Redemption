<?php

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Classroom;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of enrollments with filters.
     */
    public function index(Request $request)
    {
        $explicitAcademicYearId = $request->get('academic_year_id');
        $academicYearId = $explicitAcademicYearId;
        $branchId = $request->get('branch_id');
        $classId = $request->get('class_id');
        $sectionId = $request->get('section_id');
        $status = $request->get('status');
        $feeStatus = $request->get('fee_status');
        $search = $request->get('search', '');

        // Default to current academic year for enrollment queries
        if (!$academicYearId) {
            $currentAy = AcademicYear::where('is_current', true)->first()
                ?? AcademicYear::orderBy('id', 'desc')->first();
            $academicYearId = $currentAy?->id;
        }

        $query = StudentEnrollment::with(['student', 'academicYear', 'branch', 'classroom', 'section']);

        // Branch scope: principals only see enrollments in their branch
        $branchScope = $request->attributes->get('branch_scope');
        if ($branchScope) {
            $query->where('branch_id', $branchScope);
        }

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        if ($classId) {
            $query->where('class_id', $classId);
        }
        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($feeStatus) {
            $query->where('registration_fee_status', $feeStatus);
        }
        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('full_name', 'LIKE', "%{$search}%")
                    ->orWhere('admission_number', 'LIKE', "%{$search}%")
                    ->orWhere('roll_number', 'LIKE', "%{$search}%");
            });
        }

        $enrollments = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        // Branch scoping for fee stats: principals see their branch only, GM sees all
        $statsBranchId = $branchScope ?? $branchId;

        $totalEnrolled = StudentEnrollment::where('academic_year_id', $academicYearId)->where('status', 'enrolled')
            ->when($statsBranchId, fn($q) => $q->where('branch_id', $statsBranchId))
            ->count();
        $feePaid = StudentEnrollment::where('academic_year_id', $academicYearId)->where('registration_fee_status', 'paid')
            ->when($statsBranchId, fn($q) => $q->where('branch_id', $statsBranchId))
            ->count();
        $feeUnpaid = StudentEnrollment::where('academic_year_id', $academicYearId)->whereIn('registration_fee_status', ['unpaid', 'partial'])
            ->when($statsBranchId, fn($q) => $q->where('branch_id', $statsBranchId))
            ->count();
        $feeWaived = StudentEnrollment::where('academic_year_id', $academicYearId)->where('registration_fee_status', 'waived')
            ->when($statsBranchId, fn($q) => $q->where('branch_id', $statsBranchId))
            ->count();
        $totalFeeCollected = StudentEnrollment::where('academic_year_id', $academicYearId)->where('registration_fee_status', 'paid')
            ->when($statsBranchId, fn($q) => $q->where('branch_id', $statsBranchId))
            ->sum('registration_fee_paid');

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $branches = Branch::where('is_active', true)->get();

        // Classes for filter dropdown: always filter by the effective academic year
        // so the dropdown matches the enrollment data being shown.
        $effectiveAyId = $academicYearId;
        $classesQuery = Classroom::with('sections')
            ->where('academic_year_id', $effectiveAyId);

        // Also filter classes by branch if branch filter is active
        $effectiveBranchId = $branchScope ?? $branchId;
        if ($effectiveBranchId) {
            $classesQuery->where('branch_id', $effectiveBranchId);
        }
        $classes = $classesQuery->orderBy('name')->get();
        $sections = $classId ? Section::where('class_id', $classId)->get() : collect();

        // For branch principals, auto-limit branches to their own
        if ($branchScope) {
            $branches = Branch::where('id', $branchScope)->get();
        }

        return view('admin.Enrollment.index', compact(
            'enrollments', 'academicYears', 'branches', 'classes', 'sections',
            'academicYearId', 'branchId', 'classId', 'sectionId', 'status', 'feeStatus', 'search',
            'totalEnrolled', 'feePaid', 'feeUnpaid', 'feeWaived', 'totalFeeCollected'
        ));
    }

    /**
     * Show the form for creating a new enrollment (single student).
     */
    public function create()
    {
        $branchScope = request()->attributes->get('branch_scope');

        // Include active, transferred, and graduated students who can be enrolled/re-enrolled
        $studentsQuery = Student::whereIn('status', ['active', 'transferred', 'graduated'])
            ->orderBy('full_name');

        if ($branchScope) {
            $studentsQuery->where('branch_id', $branchScope);
        }

        $students = $studentsQuery->get();

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $branches = Branch::where('is_active', true)->get();
        if ($branchScope) {
            $branches = Branch::where('id', $branchScope)->get();
        }

        // Filter classes by branch scope
        $classesQuery = Classroom::with('sections');
        if ($branchScope) {
            $classesQuery->where('branch_id', $branchScope);
        }
        $classes = $classesQuery->orderBy('name')->get();

        return view('admin.Enrollment.create', compact('students', 'academicYears', 'branches', 'classes'));
    }

    /**
     * Store a new enrollment for a single student.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'branch_id' => 'required|exists:branches,id',
            'section_id' => 'required|exists:sections,id',
            'registration_fee' => 'required|numeric|min:0',
            'enrollment_type' => 'nullable|in:new,returning,transferred_in',
            'enrollment_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $section = Section::find($validated['section_id']);
        $validated['class_id'] = $section->class_id;

        // Check for duplicate enrollment
        $existing = StudentEnrollment::where('student_id', $validated['student_id'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->first();

        if ($existing) {
            return back()->withInput()->with('error', 'This student is already enrolled in the selected academic year.');
        }

        // Get roll number from student
        $student = Student::find($validated['student_id']);
        $validated['roll_number'] = $student->roll_number;
        $validated['enrollment_date'] = $validated['enrollment_date'] ?? now()->toDateString();
        $validated['enrollment_type'] = $validated['enrollment_type'] ?? 'new';
        $validated['status'] = 'enrolled';
        $validated['enrolled_by'] = auth()->id();

        // Registration fee defaults
        $validated['registration_fee_paid'] = 0;
        $validated['registration_fee_status'] = $validated['registration_fee'] > 0 ? 'unpaid' : 'waived';

        StudentEnrollment::create($validated);

        // Update student's current class/section/ay if this is the current academic year
        $this->syncStudentToEnrollment($student, $validated);

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'Student enrolled successfully!');
    }

    /**
     * Display the specified enrollment.
     */
    public function show(StudentEnrollment $enrollment)
    {
        $enrollment->load(['student', 'academicYear', 'branch', 'classroom', 'section', 'enrolledBy']);

        // Get all enrollments for this student (enrollment history)
        $enrollmentHistory = StudentEnrollment::where('student_id', $enrollment->student_id)
            ->with(['academicYear', 'branch', 'classroom', 'section'])
            ->orderBy('academic_year_id', 'desc')
            ->get();

        return view('admin.Enrollment.show', compact('enrollment', 'enrollmentHistory'));
    }

    /**
     * Show the form for editing the specified enrollment.
     */
    public function edit(StudentEnrollment $enrollment)
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $branches = Branch::where('is_active', true)->get();
        $classes = Classroom::with('sections')->get();
        $sections = Section::where('class_id', $enrollment->class_id)->get();

        return view('admin.Enrollment.edit', compact('enrollment', 'academicYears', 'branches', 'classes', 'sections'));
    }

    /**
     * Update the specified enrollment.
     */
    public function update(Request $request, StudentEnrollment $enrollment)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'section_id' => 'required|exists:sections,id',
            'status' => 'required|in:enrolled,pending,withdrawn,graduated,transferred',
            'registration_fee' => 'required|numeric|min:0',
            'registration_fee_paid' => 'required|numeric|min:0',
            'registration_fee_status' => 'required|in:unpaid,partial,paid,waived',
            'registration_fee_payment_method' => 'nullable|in:cash,bank,mobile,cheque,online',
            'registration_fee_receipt_number' => 'nullable|string|max:100',
            'registration_fee_notes' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $section = Section::find($validated['section_id']);
        $validated['class_id'] = $section->class_id;

        $enrollment->update($validated);

        // Sync student if status changed
        if ($validated['status'] === 'withdrawn') {
            $enrollment->student->update([
                'status' => 'inactive',
                'leave_date' => now()->toDateString(),
                'leave_reason' => 'Withdrawn from academic year ' . $enrollment->academicYear->name,
            ]);
        } elseif ($validated['status'] === 'enrolled') {
            $this->syncStudentToEnrollment($enrollment->student, $validated);
            $enrollment->student->update(['status' => 'active']);
        }

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'Enrollment updated successfully!');
    }

    /**
     * Remove the specified enrollment.
     */
    public function destroy(StudentEnrollment $enrollment)
    {
        $enrollment->delete();
        return redirect()->route('admin.enrollments.index')
            ->with('success', 'Enrollment deleted successfully!');
    }

    /**
     * Show the bulk enrollment form for enrolling students in a new academic year.
     */
    public function bulkEnroll()
    {
        $branchScope = request()->attributes->get('branch_scope');

        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $branches = Branch::where('is_active', true)->get();

        if ($branchScope) {
            $branches = Branch::where('id', $branchScope)->get();
        }

        return view('admin.Enrollment.bulk-enroll', compact('academicYears', 'branches'));
    }

    /**
     * Process bulk enrollment: carry forward active students to a new academic year.
     */
    public function processBulkEnroll(Request $request)
    {
        $validated = $request->validate([
            'source_academic_year_id' => 'required|exists:academic_years,id',
            'target_academic_year_id' => 'required|exists:academic_years,id|different:source_academic_year_id',
            'registration_fee' => 'required|numeric|min:0',
            'branch_id' => 'nullable|exists:branches,id',
            'auto_promote' => 'nullable|boolean',
            'enrollment_date' => 'nullable|date',
        ]);

        $sourceAyId = $validated['source_academic_year_id'];
        $targetAyId = $validated['target_academic_year_id'];
        $registrationFee = $validated['registration_fee'];
        $autoPromote = $validated['auto_promote'] ?? false;
        $enrollmentDate = $validated['enrollment_date'] ?? now()->toDateString();

        // Branch scope: principals can only bulk-enroll from their branch
        $branchScope = $request->attributes->get('branch_scope');

        // Get all currently enrolled students from the source academic year
        $sourceEnrollments = StudentEnrollment::where('academic_year_id', $sourceAyId)
            ->where('status', 'enrolled');

        if ($branchScope) {
            $sourceEnrollments->where('branch_id', $branchScope);
        } elseif (!empty($validated['branch_id'])) {
            $sourceEnrollments->where('branch_id', $validated['branch_id']);
        }

        $sourceEnrollments = $sourceEnrollments->get();

        if ($sourceEnrollments->isEmpty()) {
            return back()->with('error', 'No enrolled students found in the source academic year.');
        }

        $enrolled = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($sourceEnrollments as $sourceEnrollment) {
                // Check if already enrolled in target year
                $exists = StudentEnrollment::where('student_id', $sourceEnrollment->student_id)
                    ->where('academic_year_id', $targetAyId)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // Determine new class/section (same or promoted)
                $newClassId = $sourceEnrollment->class_id;
                $newSectionId = $sourceEnrollment->section_id;
                $enrollmentType = 'returning';

                if ($autoPromote) {
                    $promoted = $this->getPromotedClassSection(
                        $sourceEnrollment->class_id,
                        $sourceEnrollment->section_id,
                        $sourceEnrollment->branch_id,
                        $targetAyId
                    );
                    if ($promoted) {
                        $newClassId = $promoted['class_id'];
                        $newSectionId = $promoted['section_id'];
                    }
                }

                StudentEnrollment::create([
                    'student_id' => $sourceEnrollment->student_id,
                    'academic_year_id' => $targetAyId,
                    'branch_id' => $sourceEnrollment->branch_id,
                    'class_id' => $newClassId,
                    'section_id' => $newSectionId,
                    'roll_number' => $sourceEnrollment->roll_number,
                    'enrollment_date' => $enrollmentDate,
                    'status' => 'enrolled',
                    'enrollment_type' => $enrollmentType,
                    'registration_fee' => $registrationFee,
                    'registration_fee_paid' => 0,
                    'registration_fee_status' => $registrationFee > 0 ? 'unpaid' : 'waived',
                    'enrolled_by' => auth()->id(),
                ]);

                $enrolled++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk enrollment failed: ' . $e->getMessage());
        }

        $message = "Bulk enrollment completed: {$enrolled} students enrolled";
        if ($skipped > 0) {
            $message .= ", {$skipped} skipped (already enrolled)";
        }

        return redirect()->route('admin.enrollments.index', ['academic_year_id' => $targetAyId])
            ->with('success', $message);
    }

    /**
     * Show the form for recording a registration fee payment.
     */
    public function payRegistrationFee(StudentEnrollment $enrollment)
    {
        return view('admin.Enrollment.pay-fee', compact('enrollment'));
    }

    /**
     * Process a registration fee payment.
     */
    public function processPayRegistrationFee(Request $request, StudentEnrollment $enrollment)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,bank,mobile,cheque,online',
            'receipt_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $enrollment->payRegistrationFee(
            $validated['amount'],
            $validated['payment_method'],
            $validated['receipt_number'],
            $validated['notes']
        );

        return redirect()->route('admin.enrollments.show', $enrollment)
            ->with('success', 'Registration fee payment recorded successfully!');
    }

    /**
     * Waive the registration fee for an enrollment.
     */
    public function waiveRegistrationFee(StudentEnrollment $enrollment)
    {
        $enrollment->update([
            'registration_fee_status' => 'waived',
            'registration_fee_notes' => 'Fee waived by ' . auth()->user()->name . ' on ' . now()->toDateString(),
        ]);

        return redirect()->route('admin.enrollments.show', $enrollment)
            ->with('success', 'Registration fee waived successfully!');
    }

    /**
     * Withdraw a student from an enrollment.
     */
    public function withdraw(StudentEnrollment $enrollment)
    {
        return view('admin.Enrollment.withdraw', compact('enrollment'));
    }

    /**
     * Process student withdrawal.
     */
    public function processWithdraw(Request $request, StudentEnrollment $enrollment)
    {
        $validated = $request->validate([
            'withdrawal_reason' => 'required|string|max:500',
            'withdrawal_date' => 'nullable|date',
        ]);

        $enrollment->withdraw($validated['withdrawal_reason'], $validated['withdrawal_date'] ?? null);

        return redirect()->route('admin.enrollments.index')
            ->with('success', 'Student withdrawn successfully.');
    }

    // ── API Endpoints ──────────────────────────────────────────────

    /**
     * Get sections for a class (AJAX).
     */
    public function apiSections(Request $request)
    {
        $classId = $request->get('class_id');
        $sections = Section::where('class_id', $classId)->get(['id', 'name', 'max_students']);
        return response()->json($sections);
    }

    /**
     * Get branches for an academic year (AJAX).
     * Returns branches that have enrollments in the given academic year.
     */
    public function apiBranches(Request $request)
    {
        $academicYearId = $request->get('academic_year_id');

        // Branch scope: principals only see their branch
        $branchScope = $request->attributes->get('branch_scope');

        if ($branchScope) {
            return response()->json(Branch::where('id', $branchScope)->where('is_active', true)->get());
        }

        $branches = Branch::where('is_active', true)
            ->when($academicYearId, function ($q) use ($academicYearId) {
                $q->whereHas('enrollments', function ($q2) use ($academicYearId) {
                    $q2->where('academic_year_id', $academicYearId);
                });
            })
            ->orderBy('name')
            ->get();

        return response()->json($branches);
    }

    /**
     * Get classes for a branch (AJAX).
     */
    public function apiClasses(Request $request)
    {
        $branchId = $request->get('branch_id');
        $academicYearId = $request->get('academic_year_id');

        // Default to current academic year if none provided
        if (!$academicYearId) {
            $currentAy = AcademicYear::where('is_current', true)->first()
                ?? AcademicYear::orderBy('id', 'desc')->first();
            $academicYearId = $currentAy?->id;
        }

        // Branch scope: principals only see their branch classes
        $branchScope = $request->attributes->get('branch_scope');
        $effectiveBranchId = $branchScope ?? $branchId;

        $classes = Classroom::when($effectiveBranchId, fn($q) => $q->where('branch_id', $effectiveBranchId))
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->with('sections')
            ->orderBy('name')
            ->get();
        return response()->json($classes);
    }

    /**
     * Get unenrolled students for an academic year (AJAX).
     */
    public function apiUnenrolledStudents(Request $request)
    {
        $academicYearId = $request->get('academic_year_id');
        $branchId = $request->get('branch_id');

        $enrolledStudentIds = StudentEnrollment::where('academic_year_id', $academicYearId)
            ->pluck('student_id');

        $students = Student::whereIn('status', ['active', 'transferred', 'graduated'])
            ->whereNotIn('id', $enrolledStudentIds)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'admission_number', 'branch_id', 'status']);

        return response()->json($students);
    }

    /**
     * Get enrollment summary stats for an academic year (AJAX).
     */
    public function apiStats(Request $request)
    {
        $academicYearId = $request->get('academic_year_id');

        return response()->json([
            'total_enrolled' => StudentEnrollment::where('academic_year_id', $academicYearId)->where('status', 'enrolled')->count(),
            'fee_paid' => StudentEnrollment::where('academic_year_id', $academicYearId)->where('registration_fee_status', 'paid')->count(),
            'fee_unpaid' => StudentEnrollment::where('academic_year_id', $academicYearId)->whereIn('registration_fee_status', ['unpaid', 'partial'])->count(),
            'fee_waived' => StudentEnrollment::where('academic_year_id', $academicYearId)->where('registration_fee_status', 'waived')->count(),
            'total_fee_collected' => StudentEnrollment::where('academic_year_id', $academicYearId)->sum('registration_fee_paid'),
            'total_fee_expected' => StudentEnrollment::where('academic_year_id', $academicYearId)->sum('registration_fee'),
        ]);
    }

    // ── Private Helpers ────────────────────────────────────────────

    /**
     * Sync the student's main record with the enrollment data.
     */
    private function syncStudentToEnrollment(Student $student, array $data): void
    {
        $currentAy = AcademicYear::where('is_current', true)->first()
            ?? AcademicYear::orderBy('id', 'desc')->first();

        if (isset($data['academic_year_id']) && $data['academic_year_id'] == $currentAy?->id) {
            $student->update([
                'branch_id' => $data['branch_id'] ?? $student->branch_id,
                'class_id' => $data['class_id'] ?? $student->class_id,
                'section_id' => $data['section_id'] ?? $student->section_id,
                'academic_year_id' => $data['academic_year_id'],
            ]);
        }
    }

    /**
     * Determine the next grade/section for promotion.
     * E.g., Grade 3 Section A → Grade 4 Section A
     */
    private function getPromotedClassSection(int $currentClassId, int $currentSectionId, int $branchId, int $targetAyId): ?array
    {
        $currentClass = Classroom::find($currentClassId);
        $currentSection = Section::find($currentSectionId);

        if (!$currentClass) return null;

        $currentGrade = (int) ($currentClass->numeric_name ?? 0);
        $nextGrade = $currentGrade + 1;

        // Find section letter from current section name
        $sectionLetter = 'A';
        if ($currentSection && preg_match('/([A-Z])$/i', $currentSection->name, $m)) {
            $sectionLetter = strtoupper($m[1]);
        }

        // Find the next grade class in the target academic year
        $nextClass = Classroom::where('branch_id', $branchId)
            ->where('academic_year_id', $targetAyId)
            ->where('numeric_name', $nextGrade)
            ->first();

        if (!$nextClass) {
            // If no next grade exists, keep in same grade
            $nextClass = Classroom::where('branch_id', $branchId)
                ->where('academic_year_id', $targetAyId)
                ->where('numeric_name', $currentGrade)
                ->first();

            if (!$nextClass) return null;
        }

        // Find matching section in the new class
        $nextSection = Section::where('class_id', $nextClass->id)
            ->where('name', 'LIKE', "%{$sectionLetter}")
            ->first();

        if (!$nextSection) {
            // Fallback: get first section
            $nextSection = Section::where('class_id', $nextClass->id)->first();
        }

        return [
            'class_id' => $nextClass->id,
            'section_id' => $nextSection?->id,
        ];
    }

    /**
     * Sync/backfill: Create enrollment records for students who don't have them.
     */
    public function syncEnrollments()
    {
        $currentAy = AcademicYear::where('is_current', true)->first()
            ?? AcademicYear::orderBy('id', 'desc')->first();

        if (!$currentAy) {
            return redirect()->route('admin.enrollments.index')
                ->with('error', 'No academic year found. Please create an academic year first.');
        }

        // Branch scope
        $branchScope = request()->attributes->get('branch_scope');

        // Find students without enrollment in current AY
        $studentsWithoutEnrollment = Student::whereNotExists(function ($q) use ($currentAy) {
            $q->selectRaw(1)
                ->from('student_enrollments')
                ->whereColumn('student_enrollments.student_id', 'students.id')
                ->where('academic_year_id', $currentAy->id);
        })->where('status', 'active');

        if ($branchScope) {
            $studentsWithoutEnrollment->where('branch_id', $branchScope);
        }

        $studentsWithoutEnrollment = $studentsWithoutEnrollment->get();

        if ($studentsWithoutEnrollment->isEmpty()) {
            return redirect()->route('admin.enrollments.index')
                ->with('success', 'All active students already have enrollment records.');
        }

        $created = 0;
        foreach ($studentsWithoutEnrollment as $student) {
            StudentEnrollment::create([
                'student_id' => $student->id,
                'academic_year_id' => $currentAy->id,
                'branch_id' => $student->branch_id,
                'class_id' => $student->class_id,
                'section_id' => $student->section_id,
                'roll_number' => $student->roll_number,
                'enrollment_date' => $student->admission_date ?? now()->toDateString(),
                'status' => 'enrolled',
                'enrollment_type' => 'new',
                'registration_fee' => 0,
                'registration_fee_paid' => 0,
                'registration_fee_status' => 'waived',
                'enrolled_by' => auth()->id(),
            ]);
            $created++;
        }

        return redirect()->route('admin.enrollments.index')
            ->with('success', "Synced {$created} students to academic year {$currentAy->name}.");
    }
}
