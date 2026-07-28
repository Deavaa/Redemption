<?php

namespace App\Http\Controllers\Enrollment;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\ClassRoom;
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

        // Classes for filter dropdown: try to filter by the effective academic year.
        // If no classes exist for that academic year, fall back to showing all
        // classes for the branch so the dropdown is never empty.
        $effectiveAyId = $academicYearId;
        $effectiveBranchId = $branchScope ?? $branchId;

        $classesQuery = ClassRoom::with('sections')
            ->where('academic_year_id', $effectiveAyId);
        if ($effectiveBranchId) {
            $classesQuery->where('branch_id', $effectiveBranchId);
        }
        $classes = $classesQuery->orderBy('numeric_name')->orderBy('name')->get();

        // Fallback: if no classes found for this AY, show all classes for the branch
        if ($classes->isEmpty()) {
            $fallbackQuery = ClassRoom::with('sections');
            if ($effectiveBranchId) {
                $fallbackQuery->where('branch_id', $effectiveBranchId);
            }
            $classes = $fallbackQuery->orderBy('numeric_name')->orderBy('name')->get();
        }

        $sections = $classId ? Section::where('class_id', $classId)->orderBy('name')->get() : collect();

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

        // Filter classes by branch scope, with academic year filtering + fallback
        $currentAy = AcademicYear::where('is_current', true)->first()
            ?? AcademicYear::orderBy('id', 'desc')->first();

        $classesQuery = ClassRoom::with('sections');
        if ($branchScope) {
            $classesQuery->where('branch_id', $branchScope);
        }
        if ($currentAy) {
            $classesQuery->where('academic_year_id', $currentAy->id);
        }
        $classes = $classesQuery->orderBy('numeric_name')->orderBy('name')->get();

        // Fallback: if no classes for current AY, show all classes for the branch
        if ($classes->isEmpty()) {
            $fallbackQuery = ClassRoom::with('sections');
            if ($branchScope) {
                $fallbackQuery->where('branch_id', $branchScope);
            }
            $classes = $fallbackQuery->orderBy('numeric_name')->orderBy('name')->get();
        }

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
            'fee_discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_reason' => 'nullable|string|max:255',
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
        $validated['fee_discount'] = $validated['fee_discount'] ?? 0;
        $validated['discount_type'] = $validated['discount_type'] ?? 'fixed';
        $validated['registration_fee_paid'] = 0;

        // Calculate effective fee after discount
        $effectiveFee = $validated['registration_fee'];
        if ($validated['discount_type'] === 'percentage' && $validated['fee_discount'] > 0) {
            $effectiveFee = max(0, $validated['registration_fee'] - ($validated['registration_fee'] * $validated['fee_discount'] / 100));
        } else {
            $effectiveFee = max(0, $validated['registration_fee'] - $validated['fee_discount']);
        }
        $validated['registration_fee_status'] = $effectiveFee > 0 ? 'unpaid' : 'waived';

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

        // Try to load classes for the enrollment's academic year, with fallback
        $classesQuery = ClassRoom::with('sections')
            ->where('academic_year_id', $enrollment->academic_year_id);
        $classes = $classesQuery->orderBy('numeric_name')->orderBy('name')->get();

        if ($classes->isEmpty()) {
            $classes = ClassRoom::with('sections')->orderBy('numeric_name')->orderBy('name')->get();
        }

        $sections = Section::where('class_id', $enrollment->class_id)->orderBy('name')->get();

        return view('admin.Enrollment.edit', compact('enrollment', 'academicYears', 'branches', 'classes', 'sections'));
    }

    /**
     * Update the specified enrollment.
     */
    public function update(Request $request, StudentEnrollment $enrollment)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'status' => 'required|in:enrolled,pending,withdrawn,graduated,transferred',
            'registration_fee' => 'required|numeric|min:0',
            'fee_discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percentage,fixed',
            'discount_reason' => 'nullable|string|max:255',
            'registration_fee_paid' => 'required|numeric|min:0',
            'registration_fee_status' => 'required|in:unpaid,partial,paid,waived',
            'registration_fee_payment_method' => 'nullable|in:cash,bank,mobile,cheque,online',
            'registration_fee_receipt_number' => 'nullable|string|max:100',
            'registration_fee_notes' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Use the selected class_id directly (from the dropdown)
        // Also sync section to match the class
        $section = Section::find($validated['section_id']);
        if ($section && $section->class_id != $validated['class_id']) {
            // Section doesn't belong to the selected class — find first section of the class
            $firstSection = Section::where('class_id', $validated['class_id'])->first();
            if ($firstSection) {
                $validated['section_id'] = $firstSection->id;
            }
        }

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
        $graduated = 0;
        $errors = [];
        $systemErrors = [];

        DB::beginTransaction();
        try {
            foreach ($sourceEnrollments as $sourceEnrollment) {
                $studentName = $sourceEnrollment->student?->full_name ?? "Student #{$sourceEnrollment->student_id}";

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
                    // ── Check if student is in grade 12 → graduate instead of promoting ──
                    $currentClass = ClassRoom::find($sourceEnrollment->class_id);
                    $currentGrade = (int) ($currentClass?->numeric_name ?? 0);

                    if ($currentGrade >= 12) {
                        // Graduate the student
                        $student = Student::find($sourceEnrollment->student_id);
                        if ($student) {
                            $student->status = 'graduated';
                            $student->save();
                            // Also mark the source enrollment as graduated
                            $sourceEnrollment->update(['status' => 'graduated']);

                            // ── Auto-generate transcript for grades 9-12 ──
                            $this->autoGenerateTranscript($student);
                        }
                        $graduated++;
                        continue; // Skip creating a new enrollment for graduated students
                    }

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

                try {
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

                    // ── SYNC the student's class_id/section_id to match the new enrollment ──
                    // This ensures the student's main record reflects the promoted class,
                    // not just the enrollment record. Section may be null (pending assignment).
                    $student = Student::find($sourceEnrollment->student_id);
                    if ($student) {
                        $student->class_id = $newClassId;
                        // Only update section_id if we have a valid one
                        if ($newSectionId) {
                            $student->section_id = $newSectionId;
                        }
                        $student->academic_year_id = $targetAyId;
                        $student->save();
                    }

                    $enrolled++;
                } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                    $errors[] = "{$studentName}: Already has an enrollment record in the target year. Skipped.";
                    continue;
                } catch (\Exception $e) {
                    $systemErrors[] = "{$studentName}: Failed to create enrollment - " . $e->getMessage();
                    continue;
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk enrollment failed completely: ' . $e->getMessage());
        }

        // Build categorized error details for display
        $errorDetails = [];
        $totalFailed = count($errors) + count($systemErrors);

        if (!empty($errors)) {
            $errorDetails[] = ['type' => 'duplicate', 'label' => 'Already Enrolled (' . count($errors) . ')', 'items' => $errors];
        }
        if (!empty($systemErrors)) {
            $errorDetails[] = ['type' => 'system', 'label' => 'System Errors (' . count($systemErrors) . ')', 'items' => $systemErrors];
        }

        if ($enrolled === 0 && $totalFailed > 0) {
            return redirect()->route('admin.enrollments.bulk-enroll')
                ->with('error', "No students were enrolled. {$totalFailed} student(s) had problems.")
                ->with('bulk_error_details', $errorDetails);
        } elseif ($totalFailed > 0) {
            return redirect()->route('admin.enrollments.index', ['academic_year_id' => $targetAyId])
                ->with('warning', "Partially completed: {$enrolled} student(s) enrolled, {$skipped} skipped (already enrolled), {$totalFailed} had problems.")
                ->with('bulk_error_details', $errorDetails);
        }

        $message = "Bulk enrollment completed: {$enrolled} students enrolled";
        if ($skipped > 0) {
            $message .= ", {$skipped} skipped (already enrolled)";
        }
        if ($graduated > 0) {
            $message .= ", {$graduated} graduated (Grade 12)";
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
        $sections = Section::where('class_id', $classId)->orderBy('name')->get(['id', 'name', 'max_students']);
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
     * Falls back to showing all classes for the branch if none match the academic year.
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

        $classes = ClassRoom::when($effectiveBranchId, fn($q) => $q->where('branch_id', $effectiveBranchId))
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $academicYearId))
            ->with('sections')
            ->orderBy('numeric_name')
            ->orderBy('name')
            ->get();

        // Fallback: if no classes found for this AY, show all classes for the branch
        if ($classes->isEmpty() && $academicYearId) {
            $classes = ClassRoom::when($effectiveBranchId, fn($q) => $q->where('branch_id', $effectiveBranchId))
                ->with('sections')
                ->orderBy('numeric_name')
                ->orderBy('name')
                ->get();
        }

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
        $currentClass = ClassRoom::find($currentClassId);
        $currentSection = Section::find($currentSectionId);

        if (!$currentClass) return null;

        $currentGrade = (int) ($currentClass->numeric_name ?? 0);
        $nextGrade = $currentGrade + 1;

        // ── Grade 12 (or highest grade) → GRADUATE the student ──
        // If current grade is 12 (or there is no next grade class), mark as graduated
        if ($currentGrade >= 12) {
            // Graduate the student — set status to 'graduated'
            $student = Student::where('class_id', $currentClassId)->first();
            // Note: we return null here so the enrollment is NOT created
            // The student should be graduated instead of re-enrolled
            return null;
        }

        // Find section letter from current section name
        $sectionLetter = 'A';
        if ($currentSection && preg_match('/([A-Z])$/i', $currentSection->name, $m)) {
            $sectionLetter = strtoupper($m[1]);
        }

        // Find the next grade class in the target academic year
        $nextClass = ClassRoom::where('branch_id', $branchId)
            ->where('academic_year_id', $targetAyId)
            ->whereRaw('CAST(numeric_name AS UNSIGNED) = ?', [$nextGrade])
            ->first();

        if (!$nextClass) {
            // If no next grade exists, keep in same grade
            $nextClass = ClassRoom::where('branch_id', $branchId)
                ->where('academic_year_id', $targetAyId)
                ->whereRaw('CAST(numeric_name AS UNSIGNED) = ?', [$currentGrade])
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
     * Also handles students whose class_id doesn't belong to the current AY
     * by finding the matching class for the current AY or keeping the original.
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
        $skipped = 0;
        foreach ($studentsWithoutEnrollment as $student) {
            // Determine the class_id for enrollment: try to find a matching class
            // in the current AY, otherwise use the student's current class_id
            $classId = $student->class_id;
            $sectionId = $student->section_id;

            if ($classId) {
                $existingClass = ClassRoom::find($classId);
                // If the student's class belongs to a different AY, try to find
                // the equivalent class in the current AY (same grade + branch)
                if ($existingClass && $existingClass->academic_year_id != $currentAy->id) {
                    $matchingClass = ClassRoom::where('branch_id', $existingClass->branch_id)
                        ->where('academic_year_id', $currentAy->id)
                        ->where('numeric_name', $existingClass->numeric_name)
                        ->first();

                    if ($matchingClass) {
                        $classId = $matchingClass->id;
                        // Try to find matching section
                        if ($sectionId) {
                            $oldSection = Section::find($sectionId);
                            if ($oldSection) {
                                $matchingSection = Section::where('class_id', $matchingClass->id)
                                    ->where('name', $oldSection->name)
                                    ->first();
                                $sectionId = $matchingSection?->id ?? Section::where('class_id', $matchingClass->id)->first()?->id;
                            }
                        }
                        if (!$sectionId) {
                            $sectionId = Section::where('class_id', $matchingClass->id)->first()?->id;
                        }
                    }
                }
            }

            // Skip students without a valid class or section
            if (!$classId) {
                $skipped++;
                continue;
            }

            StudentEnrollment::create([
                'student_id' => $student->id,
                'academic_year_id' => $currentAy->id,
                'branch_id' => $student->branch_id,
                'class_id' => $classId,
                'section_id' => $sectionId,
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

        $message = "Synced {$created} students to academic year {$currentAy->name}.";
        if ($skipped > 0) {
            $message .= " {$skipped} students skipped (no class assigned).";
        }

        return redirect()->route('admin.enrollments.index')
            ->with('success', $message);
    }

    /**
     * Bulk Fix Class/Section form — update students stuck in old grade to new grade.
     * Shows a form to select: academic year, from-class, to-class, optional to-section.
     * Lists affected students for preview.
     */
    public function bulkFixClassForm(Request $request)
    {
        $branchScope = $request->attributes->get('branch_scope');
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $activeAy = AcademicYear::where('is_current', true)->first();

        $classes = ClassRoom::when($branchScope, fn($q) => $q->where('branch_id', $branchScope))
            ->orderByRaw('CAST(numeric_name AS UNSIGNED) ASC')
            ->orderBy('name')
            ->get();

        $selectedAy = $request->filled('academic_year_id') ? AcademicYear::find($request->academic_year_id) : $activeAy;
        $fromClassId = $request->filled('from_class_id') ? $request->from_class_id : null;
        $toClassId = $request->filled('to_class_id') ? $request->to_class_id : null;
        $toSectionId = $request->filled('to_section_id') ? $request->section_id : null;

        $previewStudents = collect();
        $fromClassName = '';
        $toClassName = '';
        $toSections = collect();

        if ($fromClassId && $toClassId && $selectedAy) {
            $fromClassName = ClassRoom::find($fromClassId)?->name ?? '';
            $toClassName = ClassRoom::find($toClassId)?->name ?? '';
            $toSections = Section::where('class_id', $toClassId)->orderBy('name')->get();

            // Find students whose class_id = from_class (stuck in old grade)
            $previewStudents = Student::with(['section'])
                ->where('class_id', $fromClassId)
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get();

            // Also check enrollment records
            $enrollmentCount = StudentEnrollment::where('class_id', $fromClassId)
                ->where('academic_year_id', $selectedAy->id)
                ->where('status', 'enrolled')
                ->count();
        }

        return view('admin.Enrollment.bulk-fix-class', compact(
            'academicYears', 'classes', 'selectedAy', 'fromClassId', 'toClassId',
            'toSectionId', 'previewStudents', 'fromClassName', 'toClassName', 'toSections'
        ));
    }

    /**
     * Process bulk class/section fix — moves all students from old class to new class.
     * Updates both Student.class_id and StudentEnrollment.class_id.
     */
    public function bulkFixClass(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'from_class_id' => 'required|exists:classes,id',
            'to_class_id' => 'required|exists:classes,id|different:from_class_id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $ayId = $request->academic_year_id;
        $fromClassId = $request->from_class_id;
        $toClassId = $request->to_class_id;
        $toSectionId = $request->filled('section_id') ? $request->section_id : null;

        $fromClass = ClassRoom::find($fromClassId);
        $toClass = ClassRoom::find($toClassId);

        // If no section specified, auto-assign first section of target class
        if (!$toSectionId) {
            $firstSection = Section::where('class_id', $toClassId)->orderBy('name')->first();
            $toSectionId = $firstSection?->id;
        }

        DB::beginTransaction();
        try {
            $studentCount = 0;
            $enrollmentCount = 0;

            // 1. Update Student records (main class_id/section_id)
            $students = Student::where('class_id', $fromClassId)
                ->where('status', 'active')
                ->get();

            foreach ($students as $student) {
                $student->class_id = $toClassId;
                if ($toSectionId) {
                    $student->section_id = $toSectionId;
                }
                $student->academic_year_id = $ayId;
                $student->save();
                $studentCount++;
            }

            // 2. Update StudentEnrollment records for this academic year
            $enrollments = StudentEnrollment::where('class_id', $fromClassId)
                ->where('academic_year_id', $ayId)
                ->where('status', 'enrolled')
                ->get();

            foreach ($enrollments as $enrollment) {
                $enrollment->class_id = $toClassId;
                if ($toSectionId) {
                    $enrollment->section_id = $toSectionId;
                }
                $enrollment->save();
                $enrollmentCount++;
            }

            DB::commit();

            $message = "Bulk fix completed: {$studentCount} student(s) moved from {$fromClass->name} to {$toClass->name}";
            if ($enrollmentCount > 0) {
                $message .= ", {$enrollmentCount} enrollment record(s) updated";
            }

            return redirect()->route('admin.enrollments.bulk-fix-class')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk fix failed: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the "Graduate Grade 12 Students" form.
     * Lists all currently-enrolled Grade 12 students for the selected academic year,
     * with checkboxes to pick which ones to graduate.
     */
    public function graduateForm(Request $request)
    {
        $branchScope = $request->attributes->get('branch_scope');
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $activeAy = AcademicYear::where('is_current', true)->first();
        $selectedAyId = $request->filled('academic_year_id')
            ? $request->academic_year_id
            : ($activeAy?->id ?? ($academicYears->first()?->id ?? null));

        // Find Grade 12 classes (numeric_name = 12)
        $grade12ClassesQuery = ClassRoom::whereRaw('CAST(numeric_name AS UNSIGNED) = 12');
        if ($branchScope) $grade12ClassesQuery->where('branch_id', $branchScope);
        $grade12Classes = $grade12ClassesQuery->orderBy('name')->get();

        // Find enrolled students in those classes for the selected AY
        $students = collect();
        $stats = ['total' => 0, 'already_graduated' => 0, 'eligible' => 0];
        if ($selectedAyId && $grade12Classes->isNotEmpty()) {
            $classIds = $grade12Classes->pluck('id')->all();
            $enrollments = StudentEnrollment::with(['student.section', 'classroom', 'branch'])
                ->where('academic_year_id', $selectedAyId)
                ->whereIn('class_id', $classIds)
                ->where('status', 'enrolled')
                ->get();
            // Also include students whose Student.class_id is in grade 12 (even if enrollment is missing)
            $studentsInGrade12 = Student::with(['section', 'classroom', 'branch'])
                ->whereIn('class_id', $classIds)
                ->whereIn('status', ['active', 'graduated'])
                ->get()
                ->keyBy('id');

            // Merge: prefer enrollment records, but include students without enrollment
            $seenIds = [];
            foreach ($enrollments as $enr) {
                if (!$enr->student) continue;
                $seenIds[] = $enr->student_id;
                $students->push((object)[
                    'student' => $enr->student,
                    'enrollment' => $enr,
                    'class_name' => $enr->classroom?->name ?? 'N/A',
                    'section_name' => $enr->section?->name ?? ($enr->student?->section?->name ?? '-'),
                    'branch_name' => $enr->branch?->name ?? '-',
                    'already_graduated' => $enr->student?->status === 'graduated',
                ]);
            }
            foreach ($studentsInGrade12 as $sid => $student) {
                if (in_array($sid, $seenIds)) continue;
                $students->push((object)[
                    'student' => $student,
                    'enrollment' => null,
                    'class_name' => $student->classroom?->name ?? 'N/A',
                    'section_name' => $student->section?->name ?? '-',
                    'branch_name' => $student->branch?->name ?? '-',
                    'already_graduated' => $student->status === 'graduated',
                ]);
            }
            $stats['total'] = $students->count();
            $stats['already_graduated'] = $students->filter(fn($s) => $s->already_graduated)->count();
            $stats['eligible'] = $stats['total'] - $stats['already_graduated'];
        }

        $selectedAy = $selectedAyId ? AcademicYear::find($selectedAyId) : null;

        return view('admin.Enrollment.graduate', compact(
            'academicYears', 'selectedAy', 'selectedAyId',
            'grade12Classes', 'students', 'stats'
        ));
    }

    /**
     * Process graduation: mark selected (or all) Grade 12 students as graduated.
     * Sets Student.status = 'graduated', marks their enrollment rows as graduated,
     * and auto-generates a 9-12 transcript.
     */
    public function processGraduate(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'mode' => 'required|in:all,selected',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        $ayId = $request->academic_year_id;
        $mode = $request->mode;
        $branchScope = $request->attributes->get('branch_scope');

        // Resolve target students
        $grade12ClassesQuery = ClassRoom::whereRaw('CAST(numeric_name AS UNSIGNED) = 12');
        if ($branchScope) $grade12ClassesQuery->where('branch_id', $branchScope);
        $grade12ClassIds = $grade12ClassesQuery->pluck('id')->all();

        if (empty($grade12ClassIds)) {
            return back()->with('error', 'No Grade 12 classes found. Please create a class with numeric_name = 12 first.');
        }

        // Build query for eligible students
        $studentQuery = Student::whereIn('class_id', $grade12ClassIds)
            ->where('status', 'active');
        if ($mode === 'selected') {
            if (empty($request->student_ids)) {
                return back()->with('error', 'No students selected. Please select at least one student, or use "Graduate All" mode.');
            }
            $studentQuery->whereIn('id', $request->student_ids);
        }
        $students = $studentQuery->get();

        if ($students->isEmpty()) {
            return back()->with('warning', 'No eligible Grade 12 students found to graduate. (Students already graduated are skipped.)');
        }

        DB::beginTransaction();
        try {
            $graduated = 0;
            $transcripts = 0;
            $enrollmentRows = 0;

            foreach ($students as $student) {
                $student->status = 'graduated';
                $student->leave_date = now()->toDateString();
                $student->leave_reason = 'Graduated from Grade 12';
                $student->save();

                // ── Keep the user account active so the graduate can still log in ──
                // Access is gated by the 'graduate_access_enabled' setting in
                // StudentMiddleware. The admin can revoke access at any time by
                // toggling that setting off (or by deactivating the user account).
                if ($student->user) {
                    $student->user->is_active = true;
                    $student->user->save();
                }

                // Mark their enrollment row for this AY as graduated
                $enrollments = StudentEnrollment::where('student_id', $student->id)
                    ->where('academic_year_id', $ayId)
                    ->where('status', 'enrolled')
                    ->get();
                foreach ($enrollments as $enr) {
                    $enr->status = 'graduated';
                    $enr->save();
                    $enrollmentRows++;
                }

                // Auto-generate transcript
                $beforeCertCount = \App\Models\Certificate::where('student_id', $student->id)->count();
                $this->autoGenerateTranscript($student);
                $afterCertCount = \App\Models\Certificate::where('student_id', $student->id)->count();
                if ($afterCertCount > $beforeCertCount) $transcripts++;

                $graduated++;
            }

            DB::commit();

            $msg = "Graduation complete: {$graduated} student(s) marked as graduated.";
            if ($enrollmentRows > 0) $msg .= " {$enrollmentRows} enrollment record(s) updated.";
            if ($transcripts > 0) $msg .= " {$transcripts} transcript(s) auto-generated.";

            return redirect()->route('admin.enrollments.graduate')
                ->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Graduation failed: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the "Reset Enrollments" form — used when the current AY's enrollment
     * data is messed up and the user wants to start over.
     *
     * Safety: only allows deletion of enrollment rows for the selected AY
     * (optionally filtered by class/branch). Does NOT touch students, marks,
     * attendance, or prior-year enrollments.
     */
    public function resetForm(Request $request)
    {
        $branchScope = $request->attributes->get('branch_scope');
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $activeAy = AcademicYear::where('is_current', true)->first();
        $selectedAyId = $request->filled('academic_year_id')
            ? $request->academic_year_id
            : ($activeAy?->id ?? null);

        $classes = ClassRoom::when($branchScope, fn($q) => $q->where('branch_id', $branchScope))
            ->orderByRaw('CAST(numeric_name AS UNSIGNED) ASC')
            ->orderBy('name')
            ->get();

        // Preview counts for the selected AY (with optional filters)
        $preview = ['enrollments' => 0, 'students_in_class' => 0, 'branches' => 0];
        $byStatus = [];
        if ($selectedAyId) {
            $q = StudentEnrollment::where('academic_year_id', $selectedAyId);
            if ($branchScope) $q->where('branch_id', $branchScope);
            if ($request->filled('class_id')) $q->where('class_id', $request->class_id);
            if ($request->filled('branch_id')) $q->where('branch_id', $request->branch_id);
            if ($request->filled('status')) $q->where('status', $request->status);

            $preview['enrollments'] = $q->count();
            $preview['branches'] = $q->distinct()->count('branch_id');
            $byStatus = StudentEnrollment::where('academic_year_id', $selectedAyId)
                ->when($branchScope, fn($qq) => $qq->where('branch_id', $branchScope))
                ->selectRaw('status, COUNT(*) as cnt')
                ->groupBy('status')
                ->pluck('cnt', 'status')
                ->all();
        }

        $branches = $branchScope
            ? Branch::where('id', $branchScope)->get()
            : Branch::where('is_active', true)->get();

        return view('admin.Enrollment.reset', compact(
            'academicYears', 'classes', 'branches', 'selectedAyId', 'preview', 'byStatus'
        ));
    }

    /**
     * Delete enrollment rows matching the filters. Optionally also resets
     * Student.class_id / section_id / academic_year_id back to the previous AY
     * (so students appear "not yet enrolled" in the current AY).
     *
     * Critical safety: requires confirmation string "RESET" typed by user.
     */
    public function processReset(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'branch_id' => 'nullable|exists:branches,id',
            'class_id' => 'nullable|exists:classes,id',
            'status' => 'nullable|in:enrolled,pending,withdrawn,graduated,transferred',
            'reset_student_class' => 'nullable|boolean',
            'confirmation' => 'required|in:RESET',
        ]);

        $ayId = $request->academic_year_id;
        $branchScope = $request->attributes->get('branch_scope');

        $q = StudentEnrollment::where('academic_year_id', $ayId);
        if ($branchScope) $q->where('branch_id', $branchScope);
        if ($request->filled('branch_id')) $q->where('branch_id', $request->branch_id);
        if ($request->filled('class_id')) $q->where('class_id', $request->class_id);
        if ($request->filled('status')) $q->where('status', $request->status);

        $enrollmentIds = $q->pluck('id')->all();
        $studentIds = $q->pluck('student_id')->unique()->all();
        $count = count($enrollmentIds);

        if ($count === 0) {
            return back()->with('warning', 'No enrollment rows matched the selected filters. Nothing was deleted.');
        }

        DB::beginTransaction();
        try {
            // 1. Delete the enrollment rows
            StudentEnrollment::whereIn('id', $enrollmentIds)->delete();

            // 2. Optionally reset Student.class_id / academic_year_id back to previous AY
            //    so the student appears "not yet enrolled" in this AY.
            if ($request->boolean('reset_student_class') && !empty($studentIds)) {
                $previousAy = AcademicYear::where('id', '<', $ayId)
                    ->orderByDesc('id')
                    ->first();
                // If a previous AY exists, try to restore class/section from that year's enrollment
                foreach ($studentIds as $sid) {
                    $prevEnr = $previousAy
                        ? StudentEnrollment::where('student_id', $sid)
                            ->where('academic_year_id', $previousAy->id)
                            ->where('status', 'enrolled')
                            ->orderByDesc('id')
                            ->first()
                        : null;
                    $student = Student::find($sid);
                    if (!$student) continue;

                    if ($prevEnr) {
                        $student->class_id = $prevEnr->class_id;
                        $student->section_id = $prevEnr->section_id;
                        $student->academic_year_id = $prevEnr->academic_year_id;
                    } else {
                        // No prior enrollment — just clear the current-AY pointer
                        $student->academic_year_id = $previousAy?->id ?? $student->academic_year_id;
                    }
                    // If the student was marked graduated by mistake during the messed-up enrollment,
                    // re-activate them (only if their original status before the mess was active).
                    // We are conservative: only re-activate if leave_reason mentions "Graduated from Grade 12"
                    // AND the user explicitly chose to reset student records.
                    if ($student->status === 'graduated'
                        && str_contains(strtolower($student->leave_reason ?? ''), 'graduated from grade 12')) {
                        $student->status = 'active';
                        $student->leave_date = null;
                        $student->leave_reason = null;
                    }
                    $student->save();
                }
            }

            DB::commit();

            $msg = "Reset complete: {$count} enrollment row(s) deleted for AY #{$ayId}.";
            if ($request->boolean('reset_student_class')) {
                $msg .= ' Student class/section pointers restored to previous AY where possible.';
            }
            $msg .= ' You can now re-run bulk enrollment.';

            return redirect()->route('admin.enrollments.reset')
                ->with('success', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Reset failed: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Auto-generate a transcript certificate for a graduating student.
     * Covers grades 9-12 (all marks available in the system).
     */
    private function autoGenerateTranscript(Student $student): void
    {
        try {
            $existing = \App\Models\Certificate::where('student_id', $student->id)
                ->where('type', 'transcript')->exists();
            if ($existing) return;

            $hasMarks = MarkEntry::where('student_id', $student->id)
                ->whereHas('classRoom', function ($q) {
                    $q->whereRaw('CAST(numeric_name AS UNSIGNED) >= 9')
                      ->whereRaw('CAST(numeric_name AS UNSIGNED) <= 12');
                })->exists();
            if (!$hasMarks) return;

            $prefix = 'TRA';
            $year = date('Y');
            $lastCert = \App\Models\Certificate::where('certificate_number', 'LIKE', "{$prefix}-{$year}-%")
                ->orderByDesc('id')->first();
            $nextNum = $lastCert ? ((int) end(explode('-', $lastCert->certificate_number))) + 1 : 1;
            $certNum = $prefix . '-' . $year . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            while (\App\Models\Certificate::where('certificate_number', $certNum)->exists()) {
                $nextNum++;
                $certNum = $prefix . '-' . $year . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
            }

            \App\Models\Certificate::create([
                'student_id' => $student->id,
                'type' => 'transcript',
                'certificate_number' => $certNum,
                'issue_date' => now()->format('Y-m-d'),
                'content' => 'Auto-generated graduation transcript (Grades 9-12) for ' . $student->full_name,
                'template' => 'transcript',
            ]);

            \Log::info('Graduation transcript auto-generated', [
                'student_id' => $student->id,
                'certificate_number' => $certNum,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Auto-generate transcript failed: ' . $e->getMessage());
        }
    }
}
