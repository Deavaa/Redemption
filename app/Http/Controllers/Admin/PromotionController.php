<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\GradeScale;
use App\Models\PromotionResult;
use App\Models\PromotionSetting;
use App\Models\Section;
use App\Models\Student;
use App\Models\Term;
use App\Services\PromotionService;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /**
     * Display promotion dashboard.
     */
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        $currentAy = AcademicYear::where('is_current', true)->first();
        $terms = $currentAy ? Term::where('academic_year_id', $currentAy->id)->orderBy('id')->get() : collect();

        $selectedAy = $request->filled('academic_year_id') ? AcademicYear::find($request->academic_year_id) : $currentAy;
        $selectedTerm = $request->filled('term_id') ? Term::find($request->term_id) : null;
        $selectedClass = $request->filled('class_id') ? Classroom::find($request->class_id) : null;

        // Get classes
        $user = auth()->user();
        if ($user->role === 'branch_principal' || $user->role === 'general_manager') {
            $classes = Classroom::with(['branch', 'sections'])->orderBy('name')->get();
        } else {
            $classes = Classroom::with(['branch', 'sections'])->orderBy('name')->get();
        }

        if ($selectedAy) {
            $terms = Term::where('academic_year_id', $selectedAy->id)->orderBy('id')->get();
        }

        // Get promotion results if filters applied
        $results = collect();
        $stats = ['promoted' => 0, 'detained' => 0, 'conditional' => 0, 'pending' => 0];

        if ($selectedAy && $selectedTerm && $selectedClass) {
            $query = PromotionResult::with(['student', 'fromClass', 'toClass', 'academicYear', 'term'])
                ->where('academic_year_id', $selectedAy->id)
                ->where('term_id', $selectedTerm->id)
                ->where('from_class_id', $selectedClass->id);

            $results = $query->orderBy('status')->orderByDesc('average_score')->paginate(50);
            $stats['promoted'] = PromotionResult::where('academic_year_id', $selectedAy->id)
                ->where('term_id', $selectedTerm->id)
                ->where('from_class_id', $selectedClass->id)
                ->where('status', 'promoted')->count();
            $stats['detained'] = PromotionResult::where('academic_year_id', $selectedAy->id)
                ->where('term_id', $selectedTerm->id)
                ->where('from_class_id', $selectedClass->id)
                ->where('status', 'detained')->count();
            $stats['conditional'] = PromotionResult::where('academic_year_id', $selectedAy->id)
                ->where('term_id', $selectedTerm->id)
                ->where('from_class_id', $selectedClass->id)
                ->where('status', 'conditional')->count();

            // Count students without results (pending)
            $studentsWithResults = PromotionResult::where('academic_year_id', $selectedAy->id)
                ->where('term_id', $selectedTerm->id)
                ->where('from_class_id', $selectedClass->id)
                ->pluck('student_id');
            $stats['pending'] = Student::where('class_id', $selectedClass->id)
                ->where('status', 'active')
                ->whereNotIn('id', $studentsWithResults)
                ->count();
        }

        // Get active promotion settings
        $promotionSetting = PromotionSetting::getActive();

        return view('admin.promotion.index', compact(
            'academicYears', 'terms', 'classes', 'results', 'stats',
            'selectedAy', 'selectedTerm', 'selectedClass', 'promotionSetting'
        ));
    }

    /**
     * Process promotion for an entire class.
     */
    public function processClass(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        try {
            $service = new PromotionService();
            $result = $service->processClassPromotion(
                $validated['class_id'],
                $validated['academic_year_id'],
                $validated['term_id'],
                auth()->id()
            );

            $msg = "Promotion processed: {$result['promoted']} promoted, {$result['detained']} detained, {$result['conditional']} conditional";
            if (!empty($result['errors'])) {
                $msg .= '. Errors: ' . implode(', ', $result['errors']);
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Promotion processing failed: ' . $e->getMessage());
        }
    }

    /**
     * Process promotion for a single student.
     */
    public function processStudent(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'to_class_id' => 'nullable|exists:classes,id',
            'status' => 'required|in:promoted,detained,conditional',
            'remarks' => 'nullable|string|max:1000',
        ]);

        try {
            $service = new PromotionService();
            $result = $service->processStudentPromotion(
                $validated['student_id'],
                $validated['academic_year_id'],
                $validated['term_id'],
                $validated['to_class_id'] ?? null,
                auth()->id(),
                $validated['remarks'] ?? null,
                $validated['status']
            );

            return redirect()->back()->with('success', 'Student promotion processed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Promotion failed: ' . $e->getMessage());
        }
    }

    /**
     * Show student promotion detail.
     */
    public function show(PromotionResult $promotion)
    {
        $promotion->load(['student', 'fromClass', 'toClass', 'academicYear', 'term', 'processedBy']);
        return view('admin.promotion.detail', compact('promotion'));
    }

    /**
     * Preview promotion results before processing.
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        $service = new PromotionService();
        $class = Classroom::find($validated['class_id']);
        $students = Student::where('class_id', $validated['class_id'])
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $previews = [];
        foreach ($students as $student) {
            $perf = $service->calculateStudentPerformance(
                $student->id,
                $validated['academic_year_id'],
                $validated['term_id']
            );
            $previews[] = [
                'student' => $student,
                'performance' => $perf,
            ];
        }

        $academicYear = AcademicYear::find($validated['academic_year_id']);
        $term = Term::find($validated['term_id']);
        $promotionSetting = PromotionSetting::getActive();

        return view('admin.promotion.preview', compact(
            'previews', 'class', 'academicYear', 'term', 'promotionSetting'
        ));
    }

    /**
     * Promotion settings management.
     */
    public function settings()
    {
        $settings = PromotionSetting::orderBy('id', 'desc')->get();
        $academicYears = AcademicYear::orderBy('id', 'desc')->get();
        return view('admin.promotion.settings', compact('settings', 'academicYears'));
    }

    /**
     * Store promotion settings.
     */
    public function storeSettings(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'minimum_average_for_promotion' => 'required|numeric|min:0|max:100',
            'maximum_subjects_to_fail' => 'required|integer|min:0|max:20',
            'minimum_subject_pass_mark' => 'required|numeric|min:0|max:100',
            'consider_attendance' => 'boolean',
            'minimum_attendance_percentage' => 'nullable|numeric|min:0|max:100',
            'consider_behavior' => 'boolean',
            'consider_conduct' => 'boolean',
            'minimum_conduct_score' => 'nullable|numeric|min:0|max:5',
            'auto_promote_if_pass_all' => 'boolean',
            'allow_conditional_promotion' => 'boolean',
            'conditional_promotion_min_average' => 'nullable|numeric|min:0|max:100',
            'conditional_promotion_max_failures' => 'nullable|integer|min:0|max:20',
            'description' => 'nullable|string|max:1000',
        ]);

        // Deactivate other settings if this is set to active
        if ($request->boolean('is_active', true)) {
            PromotionSetting::query()->update(['is_active' => false]);
            $validated['is_active'] = true;
        }

        PromotionSetting::create($validated);

        return redirect()->route('admin.promotion.settings')
            ->with('success', 'Promotion settings saved successfully.');
    }

    /**
     * Grade scale management.
     */
    public function gradeScales()
    {
        GradeScale::seedDefaults();
        $scales = GradeScale::orderBy('sort_order')->get();
        return view('admin.promotion.grade-scales', compact('scales'));
    }

    /**
     * Store grade scale.
     */
    public function storeGradeScale(Request $request)
    {
        $validated = $request->validate([
            'min_score' => 'required|numeric|min:0|max:100',
            'max_score' => 'required|numeric|min:0|max:100',
            'grade' => 'required|string|max:10',
            'grade_point' => 'required|numeric|min:0|max:4',
            'description' => 'nullable|string|max:255',
            'is_passing' => 'boolean',
            'sort_order' => 'integer',
        ]);

        GradeScale::create($validated);

        return redirect()->route('admin.promotion.grade-scales')
            ->with('success', 'Grade scale entry added successfully.');
    }

    /**
     * Print promotion results.
     */
    public function print(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'class_id' => 'required|exists:classes,id',
        ]);

        $results = PromotionResult::with(['student', 'fromClass', 'toClass', 'academicYear', 'term'])
            ->where('academic_year_id', $validated['academic_year_id'])
            ->where('term_id', $validated['term_id'])
            ->where('from_class_id', $validated['class_id'])
            ->orderBy('status')
            ->orderByDesc('average_score')
            ->get();

        $class = Classroom::find($validated['class_id']);
        $academicYear = AcademicYear::find($validated['academic_year_id']);
        $term = Term::find($validated['term_id']);

        return view('admin.promotion.print', compact('results', 'class', 'academicYear', 'term'));
    }
}
