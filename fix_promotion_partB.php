<?php
/**
 * Fix Promotion Part B
 * - Creates PromotionService
 * - Creates Controllers
 * - Creates Views
 * - Adds Routes
 * - Adds Sidebar Menu Item
 */

echo "=== Creating Promotion System (Part B) ===\n\n";

 $projectPath = __DIR__;
chdir($projectPath);

require $projectPath . '/vendor/autoload.php';

 $app = require_once $projectPath . '/bootstrap/app.php';
 $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\File;

// ============================================
// 1. Create PromotionService
// ============================================
echo "Step 1: Creating PromotionService...\n";

 $serviceDir = $projectPath . '/app/Services';
if (!is_dir($serviceDir)) {
    mkdir($serviceDir, 0755, true);
}

 $servicePath = $serviceDir . '/PromotionService.php';
 $serviceContent = <<<'SERVICE'
<?php

namespace App\Services;

use App\Models\GradeScale;
use App\Models\PromotionResult;
use App\Models\PromotionSetting;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Classes;
use Illuminate\Support\Facades\DB;

class PromotionService
{
    public function calculateStudentResults($studentId, $classId, $academicYearId, $examTypeId = null)
    {
        $student = Student::findOrFail($studentId);

        $query = Mark::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId);

        if ($examTypeId) {
            $query->where('exam_type_id', $examTypeId);
        }

        $marks = $query->get();

        if ($marks->isEmpty()) {
            return null;
        }

        $totalScore = $marks->sum(function ($mark) {
            return ($mark->class_score ?? 0) + ($mark->exam_score ?? 0);
        });

        $subjectCount = $marks->count();
        $averageScore = $subjectCount > 0 ? $totalScore / $subjectCount : 0;

        $subjectsPassed = 0;
        $subjectsFailed = 0;

        foreach ($marks as $mark) {
            $totalForSubject = ($mark->class_score ?? 0) + ($mark->exam_score ?? 0);
            $grade = GradeScale::getGradeForScore($totalForSubject);
            if ($grade && $grade->grade_point > 0) {
                $subjectsPassed++;
            } else {
                $subjectsFailed++;
            }
        }

        return [
            'student_id' => $studentId,
            'class_id' => $classId,
            'academic_year_id' => $academicYearId,
            'total_score' => $totalScore,
            'average_score' => round($averageScore, 2),
            'subjects_passed' => $subjectsPassed,
            'subjects_failed' => $subjectsFailed,
            'subject_count' => $subjectCount,
        ];
    }

    public function determinePromotionStatus($averageScore, $subjectsFailed, $settings = null)
    {
        if (!$settings) {
            $settings = PromotionSetting::first();
        }

        if (!$settings) {
            $settings = (object) [
                'passing_percentage' => 50,
                'allow_conditional_promotion' => true,
                'conditional_percentage' => 40,
                'max_failed_subjects_conditional' => 2,
            ];
        }

        if ($averageScore >= $settings->passing_percentage) {
            return [
                'status' => 'promoted',
                'remarks' => 'Passed - Promoted to next class',
            ];
        }

        if ($settings->allow_conditional_promotion
            && $averageScore >= $settings->conditional_percentage
            && $subjectsFailed <= $settings->max_failed_subjects_conditional) {
            return [
                'status' => 'conditional',
                'remarks' => 'Conditionally Promoted - Needs improvement in failed subjects',
            ];
        }

        return [
            'status' => 'detained',
            'remarks' => 'Detained - Did not meet promotion requirements',
        ];
    }

    public function processClassPromotion($classId, $academicYearId, $examTypeId = null)
    {
        $students = Student::where('class_id', $classId)->get();
        $settings = PromotionSetting::first();
        $results = [];

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                $calculated = $this->calculateStudentResults(
                    $student->id,
                    $classId,
                    $academicYearId,
                    $examTypeId
                );

                if ($calculated) {
                    $promotionStatus = $this->determinePromotionStatus(
                        $calculated['average_score'],
                        $calculated['subjects_failed'],
                        $settings
                    );

                    $result = PromotionResult::updateOrCreate(
                        [
                            'student_id' => $student->id,
                            'from_class_id' => $classId,
                            'academic_year_id' => $academicYearId,
                        ],
                        [
                            'to_class_id' => null,
                            'average_score' => $calculated['average_score'],
                            'total_score' => $calculated['total_score'],
                            'subjects_passed' => $calculated['subjects_passed'],
                            'subjects_failed' => $calculated['subjects_failed'],
                            'status' => $promotionStatus['status'],
                            'remarks' => $promotionStatus['remarks'],
                            'promoted_by' => auth()->id(),
                        ]
                    );

                    $results[] = $result;
                }
            }

            DB::commit();
            return $results;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function executePromotion($academicYearId, $classId = null)
    {
        $query = PromotionResult::where('academic_year_id', $academicYearId)
            ->where('status', 'promoted');

        if ($classId) {
            $query->where('from_class_id', $classId);
        }

        $promotedResults = $query->get();
        $promotedCount = 0;

        DB::beginTransaction();
        try {
            foreach ($promotedResults as $result) {
                $nextClass = $this->getNextClass($result->from_class_id);
                if ($nextClass) {
                    $student = Student::find($result->student_id);
                    if ($student) {
                        $student->class_id = $nextClass->id;
                        $student->save();

                        $result->to_class_id = $nextClass->id;
                        $result->save();

                        $promotedCount++;
                    }
                }
            }

            DB::commit();
            return $promotedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function getNextClass($currentClassId)
    {
        $currentClass = Classes::find($currentClassId);
        if (!$currentClass) {
            return null;
        }

        // Try to find next class by class_number or name increment
        $nextClass = Classes::where('class_number', '>', $currentClass->class_number ?? 0)
            ->orWhere('name', '>', $currentClass->name)
            ->orderBy('class_number', 'asc')
            ->orderBy('name', 'asc')
            ->first();

        return $nextClass;
    }
}
SERVICE;

if (file_exists($servicePath)) {
    echo "  - PromotionService already exists. Overwriting with updated version.\n";
}
file_put_contents($servicePath, $serviceContent);
echo "  - Created PromotionService.php\n";

// ============================================
// 2. Create Controllers
// ============================================
echo "\nStep 2: Creating Controllers...\n";

 $controllers = [];

 $controllers['PromotionController.php'] = <<<'CTRL'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PromotionService;
use App\Models\PromotionResult;
use App\Models\PromotionSetting;
use App\Models\Classes;
use App\Models\AcademicYear;
use App\Models\ExamType;
use App\Models\Student;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    protected $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    public function index()
    {
        $classes = Classes::all();
        $academicYears = AcademicYear::all();
        $examTypes = ExamType::all();

        return view('admin.promotion.index', compact('classes', 'academicYears', 'examTypes'));
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'exam_type_id' => 'nullable|exists:exam_types,id',
        ]);

        $results = $this->promotionService->processClassPromotion(
            $request->class_id,
            $request->academic_year_id,
            $request->exam_type_id
        );

        return redirect()->route('admin.promotion.results', [
            'class_id' => $request->class_id,
            'academic_year_id' => $request->academic_year_id,
        ])->with('success', 'Promotion calculation completed. ' . count($results) . ' students processed.');
    }

    public function results(Request $request)
    {
        $query = PromotionResult::with(['student', 'fromClass', 'academicYear']);

        if ($request->filled('class_id')) {
            $query->where('from_class_id', $request->class_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $results = $query->orderBy('average_score', 'desc')->get();
        $classes = Classes::all();
        $academicYears = AcademicYear::all();

        return view('admin.promotion.results', compact('results', 'classes', 'academicYears'));
    }

    public function detail($id)
    {
        $result = PromotionResult::with(['student', 'fromClass', 'toClass', 'academicYear', 'promotedBy'])
            ->findOrFail($id);

        return view('admin.promotion.detail', compact('result'));
    }

    public function executePromotion(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_id' => 'nullable|exists:classes,id',
        ]);

        $count = $this->promotionService->executePromotion(
            $request->academic_year_id,
            $request->class_id
        );

        return redirect()->back()->with('success', "Successfully promoted {$count} students to next class.");
    }

    public function print($id)
    {
        $result = PromotionResult::with(['student', 'fromClass', 'toClass', 'academicYear'])
            ->findOrFail($id);

        return view('admin.promotion.print', compact('result'));
    }
}
CTRL;

 $controllers['PromotionSettingController.php'] = <<<'CTRL'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromotionSetting;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class PromotionSettingController extends Controller
{
    public function index()
    {
        $settings = PromotionSetting::with('academicYear')->get();
        $academicYears = AcademicYear::all();

        return view('admin.promotion.settings', compact('settings', 'academicYears'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'passing_percentage' => 'required|numeric|min:0|max:100',
            'allow_conditional_promotion' => 'nullable|boolean',
            'conditional_percentage' => 'nullable|numeric|min:0|max:100',
            'max_failed_subjects_conditional' => 'nullable|integer|min:0',
        ]);

        PromotionSetting::create($request->all());

        return redirect()->route('admin.promotion.settings')->with('success', 'Promotion setting created successfully.');
    }

    public function update(Request $request, $id)
    {
        $setting = PromotionSetting::findOrFail($id);

        $request->validate([
            'passing_percentage' => 'required|numeric|min:0|max:100',
            'allow_conditional_promotion' => 'nullable|boolean',
            'conditional_percentage' => 'nullable|numeric|min:0|max:100',
            'max_failed_subjects_conditional' => 'nullable|integer|min:0',
        ]);

        $setting->update($request->all());

        return redirect()->route('admin.promotion.settings')->with('success', 'Promotion setting updated successfully.');
    }

    public function destroy($id)
    {
        $setting = PromotionSetting::findOrFail($id);
        $setting->delete();

        return redirect()->route('admin.promotion.settings')->with('success', 'Promotion setting deleted.');
    }
}
CTRL;

 $controllers['GradeScaleController.php'] = <<<'CTRL'
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeScale;
use Illuminate\Http\Request;

class GradeScaleController extends Controller
{
    public function index()
    {
        $gradeScales = GradeScale::orderBy('min_score', 'desc')->get();

        return view('admin.promotion.grade-scales', compact('gradeScales'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'grade' => 'required|string|max:10',
            'min_score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|min:0',
            'remark' => 'required|string|max:255',
            'grade_point' => 'required|numeric|min:0|max:4',
        ]);

        GradeScale::create($request->all());

        return redirect()->route('admin.grade-scales.index')->with('success', 'Grade scale created successfully.');
    }

    public function update(Request $request, $id)
    {
        $gradeScale = GradeScale::findOrFail($id);

        $request->validate([
            'grade' => 'required|string|max:10',
            'min_score' => 'required|numeric|min:0',
            'max_score' => 'required|numeric|min:0',
            'remark' => 'required|string|max:255',
            'grade_point' => 'required|numeric|min:0|max:4',
        ]);

        $gradeScale->update($request->all());

        return redirect()->route('admin.grade-scales.index')->with('success', 'Grade scale updated successfully.');
    }

    public function destroy($id)
    {
        $gradeScale = GradeScale::findOrFail($id);
        $gradeScale->delete();

        return redirect()->route('admin.grade-scales.index')->with('success', 'Grade scale deleted.');
    }
}
CTRL;

 $controllerDir = $projectPath . '/app/Http/Controllers/Admin';

foreach ($controllers as $filename => $content) {
    $filepath = $controllerDir . '/' . $filename;
    if (file_exists($filepath)) {
        echo "  - $filename already exists. Overwriting.\n";
    }
    file_put_contents($filepath, $content);
    echo "  - Created $filename\n";
}

// ============================================
// 3. Create Views
// ============================================
echo "\nStep 3: Creating Views...\n";

 $viewDir = $projectPath . '/resources/views/admin/promotion';
if (!is_dir($viewDir)) {
    mkdir($viewDir, 0755, true);
}

 $views = [];

// Index view - Promotion calculator form
 $views['index.blade.php'] = <<<'VIEW'
@extends('admin.layout.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Student Promotion</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Promotion</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

            <div class="row">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Calculate Promotion Results</h3>
                        </div>
                        <form action="{{ route('admin.promotion.calculate') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Class</label>
                                    <select name="class_id" class="form-control" required>
                                        <option value="">Select Class</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Academic Year</label>
                                    <select name="academic_year_id" class="form-control" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Exam Type (Optional)</label>
                                    <select name="exam_type_id" class="form-control">
                                        <option value="">All Exams</option>
                                        @foreach($examTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Calculate Promotion</button>
                                <a href="{{ route('admin.promotion.results') }}" class="btn btn-info ml-2">View Results</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Execute Promotion</h3>
                        </div>
                        <form action="{{ route('admin.promotion.execute') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <p class="text-warning">This will move promoted students to the next class. This action cannot be undone easily.</p>
                                <div class="form-group">
                                    <label>Academic Year</label>
                                    <select name="academic_year_id" class="form-control" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Class (Optional - leave empty for all classes)</label>
                                    <select name="class_id" class="form-control">
                                        <option value="">All Classes</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to execute promotion? This will change student class assignments.')">Execute Promotion</button>
                            </div>
                        </form>
                    </div>

                    <div class="card card-secondary mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Quick Links</h3>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('admin.promotion.settings') }}" class="btn btn-outline-secondary btn-block mb-2">Promotion Settings</a>
                            <a href="{{ route('admin.grade-scales.index') }}" class="btn btn-outline-secondary btn-block">Grade Scales</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
VIEW;

// Results view
 $views['results.blade.php'] = <<<'VIEW'
@extends('admin.layout.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Promotion Results</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.promotion.index') }}">Promotion</a></li>
                        <li class="breadcrumb-item active">Results</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Results</h3>
                </div>
                <form method="GET" action="{{ route('admin.promotion.results') }}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="class_id" class="form-control">
                                    <option value="">All Classes</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="academic_year_id" class="form-control">
                                    <option value="">All Academic Years</option>
                                    @foreach($academicYears as $year)
                                        <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    <option value="promoted" {{ request('status') == 'promoted' ? 'selected' : '' }}>Promoted</option>
                                    <option value="conditional" {{ request('status') == 'conditional' ? 'selected' : '' }}>Conditional</option>
                                    <option value="detained" {{ request('status') == 'detained' ? 'selected' : '' }}>Detained</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="{{ route('admin.promotion.results') }}" class="btn btn-secondary">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Results ({{ $results->count() }} students)</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Average Score</th>
                                <th>Subjects Passed</th>
                                <th>Subjects Failed</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($results as $index => $result)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $result->student->name ?? $result->student->first_name ?? 'N/A' }}</td>
                                <td>{{ $result->fromClass->name ?? 'N/A' }}</td>
                                <td>{{ $result->average_score }}</td>
                                <td><span class="badge badge-success">{{ $result->subjects_passed }}</span></td>
                                <td><span class="badge badge-danger">{{ $result->subjects_failed }}</span></td>
                                <td>
                                    @if($result->status == 'promoted')
                                        <span class="badge badge-success">Promoted</span>
                                    @elseif($result->status == 'conditional')
                                        <span class="badge badge-warning">Conditional</span>
                                    @else
                                        <span class="badge badge-danger">Detained</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.promotion.detail', $result->id) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.promotion.print', $result->id) }}" class="btn btn-sm btn-secondary" target="_blank"><i class="fas fa-print"></i></a>
                                </td>
                            </tr>
                            @endforeach
                            @if($results->isEmpty())
                            <tr>
                                <td colspan="8" class="text-center">No promotion results found. Calculate promotion results first.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
VIEW;

// Detail view
 $views['detail.blade.php'] = <<<'VIEW'
@extends('admin.layout.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Promotion Detail</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.promotion.index') }}">Promotion</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.promotion.results') }}">Results</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Student Promotion Information</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="40%">Student Name</th>
                                    <td>{{ $result->student->name ?? $result->student->first_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Current Class</th>
                                    <td>{{ $result->fromClass->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Promoted To</th>
                                    <td>{{ $result->toClass->name ?? 'Not yet promoted' }}</td>
                                </tr>
                                <tr>
                                    <th>Academic Year</th>
                                    <td>{{ $result->academicYear->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Average Score</th>
                                    <td>{{ $result->average_score }}</td>
                                </tr>
                                <tr>
                                    <th>Total Score</th>
                                    <td>{{ $result->total_score }}</td>
                                </tr>
                                <tr>
                                    <th>Subjects Passed</th>
                                    <td><span class="badge badge-success">{{ $result->subjects_passed }}</span></td>
                                </tr>
                                <tr>
                                    <th>Subjects Failed</th>
                                    <td><span class="badge badge-danger">{{ $result->subjects_failed }}</span></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($result->status == 'promoted')
                                            <span class="badge badge-success" style="font-size:14px;">PROMOTED</span>
                                        @elseif($result->status == 'conditional')
                                            <span class="badge badge-warning" style="font-size:14px;">CONDITIONAL</span>
                                        @else
                                            <span class="badge badge-danger" style="font-size:14px;">DETAINED</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Remarks</th>
                                    <td>{{ $result->remarks }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('admin.promotion.print', $result->id) }}" class="btn btn-primary btn-block" target="_blank">
                                <i class="fas fa-print"></i> Print Result
                            </a>
                            <a href="{{ route('admin.promotion.results') }}" class="btn btn-secondary btn-block mt-2">
                                <i class="fas fa-arrow-left"></i> Back to Results
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
VIEW;

// Print view
 $views['print.blade.php'] = <<<'VIEW'
<!DOCTYPE html>
<html>
<head>
    <title>Promotion Result - {{ $result->student->name ?? $result->student->first_name ?? 'Student' }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #000; padding-bottom: 15px; }
        .school-name { font-size: 24px; font-weight: bold; }
        .title { font-size: 18px; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f0f0f0; }
        .status-promoted { color: green; font-weight: bold; }
        .status-conditional { color: orange; font-weight: bold; }
        .status-detained { color: red; font-weight: bold; }
        .footer { margin-top: 50px; display: flex; justify-content: space-between; }
        .signature { text-align: center; }
        .signature-line { width: 200px; border-top: 1px solid #000; margin-top: 60px; padding-top: 5px; }
        @media print { body { margin: 0; } }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="school-name">School of Redemption</div>
        <div class="title">Student Promotion Result</div>
        <div>Academic Year: {{ $result->academicYear->name ?? 'N/A' }}</div>
    </div>

    <table>
        <tr><th>Student Name</th><td>{{ $result->student->name ?? $result->student->first_name ?? 'N/A' }}</td></tr>
        <tr><th>Current Class</th><td>{{ $result->fromClass->name ?? 'N/A' }}</td></tr>
        <tr><th>Promoted To</th><td>{{ $result->toClass->name ?? 'Pending' }}</td></tr>
        <tr><th>Average Score</th><td>{{ $result->average_score }}%</td></tr>
        <tr><th>Total Score</th><td>{{ $result->total_score }}</td></tr>
        <tr><th>Subjects Passed</th><td>{{ $result->subjects_passed }}</td></tr>
        <tr><th>Subjects Failed</th><td>{{ $result->subjects_failed }}</td></tr>
        <tr>
            <th>Status</th>
            <td class="status-{{ $result->status }}">{{ strtoupper($result->status) }}</td>
        </tr>
        <tr><th>Remarks</th><td>{{ $result->remarks }}</td></tr>
    </table>

    <div class="footer">
        <div class="signature">
            <div class="signature-line">Class Teacher</div>
        </div>
        <div class="signature">
            <div class="signature-line">Principal</div>
        </div>
    </div>
</body>
</html>
VIEW;

// Settings view
 $views['settings.blade.php'] = <<<'VIEW'
@extends('admin.layout.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Promotion Settings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.promotion.index') }}">Promotion</a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

            <div class="row">
                <div class="col-md-5">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Add New Setting</h3>
                        </div>
                        <form action="{{ route('admin.promotion.settings.store') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Academic Year</label>
                                    <select name="academic_year_id" class="form-control" required>
                                        <option value="">Select Academic Year</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Passing Percentage (%)</label>
                                    <input type="number" name="passing_percentage" class="form-control" value="50" step="0.01" min="0" max="100" required>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" name="allow_conditional_promotion" class="custom-control-input" id="allowConditional" value="1" checked>
                                        <label class="custom-control-label" for="allowConditional">Allow Conditional Promotion</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Conditional Promotion Percentage (%)</label>
                                    <input type="number" name="conditional_percentage" class="form-control" value="40" step="0.01" min="0" max="100">
                                </div>
                                <div class="form-group">
                                    <label>Max Failed Subjects (Conditional)</label>
                                    <input type="number" name="max_failed_subjects_conditional" class="form-control" value="2" min="0">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Current Settings</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Academic Year</th>
                                        <th>Pass %</th>
                                        <th>Conditional</th>
                                        <th>Cond. %</th>
                                        <th>Max Fails</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($settings as $setting)
                                    <tr>
                                        <td>{{ $setting->academicYear->name ?? 'N/A' }}</td>
                                        <td>{{ $setting->passing_percentage }}%</td>
                                        <td>{{ $setting->allow_conditional_promotion ? 'Yes' : 'No' }}</td>
                                        <td>{{ $setting->conditional_percentage }}%</td>
                                        <td>{{ $setting->max_failed_subjects_conditional }}</td>
                                        <td>
                                            <form action="{{ route('admin.promotion.settings.destroy', $setting->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this setting?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
VIEW;

// Grade scales view
 $views['grade-scales.blade.php'] = <<<'VIEW'
@extends('admin.layout.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Grade Scales</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.promotion.index') }}">Promotion</a></li>
                        <li class="breadcrumb-item active">Grade Scales</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    {{ session('success') }}
                </div>
            @endif

            <div class="row">
                <div class="col-md-5">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Add Grade Scale</h3>
                        </div>
                        <form action="{{ route('admin.grade-scales.store') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Grade</label>
                                    <input type="text" name="grade" class="form-control" placeholder="e.g. A+" required>
                                </div>
                                <div class="form-group">
                                    <label>Minimum Score</label>
                                    <input type="number" name="min_score" class="form-control" step="0.01" min="0" required>
                                </div>
                                <div class="form-group">
                                    <label>Maximum Score</label>
                                    <input type="number" name="max_score" class="form-control" step="0.01" min="0" required>
                                </div>
                                <div class="form-group">
                                    <label>Remark</label>
                                    <input type="text" name="remark" class="form-control" placeholder="e.g. Excellent" required>
                                </div>
                                <div class="form-group">
                                    <label>Grade Point</label>
                                    <input type="number" name="grade_point" class="form-control" step="0.1" min="0" max="4" required>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">Save Grade Scale</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Grade Scale List</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Grade</th>
                                        <th>Min Score</th>
                                        <th>Max Score</th>
                                        <th>Remark</th>
                                        <th>Point</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($gradeScales as $scale)
                                    <tr>
                                        <td><strong>{{ $scale->grade }}</strong></td>
                                        <td>{{ $scale->min_score }}</td>
                                        <td>{{ $scale->max_score }}</td>
                                        <td>{{ $scale->remark }}</td>
                                        <td>{{ $scale->grade_point }}</td>
                                        <td>
                                            <form action="{{ route('admin.grade-scales.destroy', $scale->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this grade scale?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
VIEW;

foreach ($views as $filename => $content) {
    $filepath = $viewDir . '/' . $filename;
    if (file_exists($filepath)) {
        echo "  - $filename already exists. Overwriting.\n";
    }
    file_put_contents($filepath, $content);
    echo "  - Created $filename\n";
}

// ============================================
// 4. Add Routes
// ============================================
echo "\nStep 4: Adding routes...\n";

 $routesFile = $projectPath . '/routes/web.php';
 $routesContent = file_get_contents($routesFile);

 $promotionRoutes = "
    // Promotion System Routes
    Route::get('/promotion', [PromotionController::class, 'index'])->name('promotion.index');
    Route::post('/promotion/calculate', [PromotionController::class, 'calculate'])->name('promotion.calculate');
    Route::get('/promotion/results', [PromotionController::class, 'results'])->name('promotion.results');
    Route::get('/promotion/detail/{id}', [PromotionController::class, 'detail'])->name('promotion.detail');
    Route::post('/promotion/execute', [PromotionController::class, 'executePromotion'])->name('promotion.execute');
    Route::get('/promotion/print/{id}', [PromotionController::class, 'print'])->name('promotion.print');
    Route::get('/promotion/settings', [PromotionSettingController::class, 'index'])->name('promotion.settings');
    Route::post('/promotion/settings', [PromotionSettingController::class, 'store'])->name('promotion.settings.store');
    Route::post('/promotion/settings/{id}', [PromotionSettingController::class, 'update'])->name('promotion.settings.update');
    Route::delete('/promotion/settings/{id}', [PromotionSettingController::class, 'destroy'])->name('promotion.settings.destroy');
    Route::get('/grade-scales', [GradeScaleController::class, 'index'])->name('grade-scales.index');
    Route::post('/grade-scales', [GradeScaleController::class, 'store'])->name('grade-scales.store');
    Route::post('/grade-scales/{id}', [GradeScaleController::class, 'update'])->name('grade-scales.update');
    Route::delete('/grade-scales/{id}', [GradeScaleController::class, 'destroy'])->name('grade-scales.destroy');
";

if (strpos($routesContent, 'promotion.index') !== false) {
    echo "  - Promotion routes already exist. Skipping.\n";
} else {
    // Add use statements at top
    $useStatements = "
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\PromotionSettingController;
use App\Http\Controllers\Admin\GradeScaleController;
";
    if (strpos($routesContent, 'PromotionController') === false) {
        // Find last use statement and add after it
        $lastUsePos = strrpos($routesContent, ';', strpos($routesContent, 'use '));
        if ($lastUsePos !== false) {
            $routesContent = substr_replace($routesContent, ";\n" . trim($useStatements), $lastUsePos + 1, 0);
        }
    }

    // Find admin route group and add inside
    if (strpos($routesContent, "Route::group(['prefix' => 'admin'") !== false) {
        // Find the end of the admin route group and add before the closing });
        $adminGroupPos = strpos($routesContent, "Route::group(['prefix' => 'admin'");
        if ($adminGroupPos !== false) {
            // Find the matching closing });
            $depth = 0;
            $pos = strpos($routesContent, '{', $adminGroupPos);
            $startPos = $pos;
            while ($pos !== false) {
                $char = $routesContent[$pos];
                if ($char === '{') $depth++;
                if ($char === '}') $depth--;
                if ($depth === 0) break;
                $pos++;
            }
            // Insert routes before the closing });
            $insertPos = $pos;
            $routesContent = substr_replace($routesContent, $promotionRoutes, $insertPos, 0);
        }
    } else {
        // No admin group found, add at end
        $routesContent .= "\nRoute::group(['prefix' => 'admin', 'middleware' => ['auth']], function () {" . $promotionRoutes . "});\n";
    }

    file_put_contents($routesFile, $routesContent);
    echo "  - Promotion routes added to web.php\n";
}

// ============================================
// 5. Add Sidebar Menu Item
// ============================================
echo "\nStep 5: Adding sidebar menu item...\n";

 $sidebarFile = null;
 $sidebarPaths = [
    $projectPath . '/resources/views/admin/layout/sidebar.blade.php',
    $projectPath . '/resources/views/admin/layout/app.blade.php',
    $projectPath . '/resources/views/layouts/admin.blade.php',
];

foreach ($sidebarPaths as $path) {
    if (file_exists($path)) {
        $sidebarFile = $path;
        break;
    }
}

if ($sidebarFile) {
    $sidebarContent = file_get_contents($sidebarFile);

    if (strpos($sidebarContent, 'promotion.index') !== false) {
        echo "  - Sidebar menu item already exists. Skipping.\n";
    } else {
        $promotionMenuItem = '                        <li class="nav-item">
                            <a href="{{ route(\'admin.promotion.index\') }}" class="nav-link {{ request()->is(\'admin/promotion*\') ? \'active\' : \'\' }}">
                                <i class="nav-icon fas fa-graduation-cap"></i>
                                <p>Promotion</p>
                            </a>
                        </li>';

        // Try to find a good insertion point - after academic or results menu items
        $inserted = false;

        // Look for common patterns in the sidebar
        $patterns = [
            '/result/i',
            '/academic/i',
            '/exam/i',
            '/student/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $sidebarContent)) {
                // Find the last occurrence and add after its closing </li>
                $lastMatchPos = 0;
                preg_match_all($pattern, $sidebarContent, $matches, PREG_OFFSET_CAPTURE);
                if (!empty($matches[0])) {
                    $lastMatch = end($matches[0]);
                    $lastMatchPos = $lastMatch[1];
                    // Find the </li> after this position
                    $liClosePos = strpos($sidebarContent, '</li>', $lastMatchPos);
                    if ($liClosePos !== false) {
                        $sidebarContent = substr_replace($sidebarContent, "\n" . $promotionMenuItem, $liClosePos + 5, 0);
                        $inserted = true;
                        break;
                    }
                }
            }
        }

        if (!$inserted) {
            // Fallback: add before the closing </ul> of the sidebar nav
            $ulClosePos = strrpos($sidebarContent, '</ul>');
            if ($ulClosePos !== false) {
                $sidebarContent = substr_replace($sidebarContent, "\n" . $promotionMenuItem . "\n", $ulClosePos, 0);
                $inserted = true;
            }
        }

        if ($inserted) {
            file_put_contents($sidebarFile, $sidebarContent);
            echo "  - Sidebar menu item added to $sidebarFile\n";
        } else {
            echo "  - WARNING: Could not add sidebar menu automatically. Please add manually.\n";
        }
    }
} else {
    echo "  - WARNING: Sidebar file not found. Menu item not added.\n";
    echo "  - Checked: " . implode(', ', $sidebarPaths) . "\n";
}

// ============================================
// 6. Clear caches
// ============================================
echo "\nStep 6: Clearing caches...\n";
echo shell_exec('php artisan route:clear 2>&1');
echo shell_exec('php artisan view:clear 2>&1');
echo shell_exec('php artisan config:clear 2>&1');
echo shell_exec('php artisan cache:clear 2>&1');

echo "\n=== Promotion System Part B Complete ===\n";
echo "The promotion system is now installed!\n";
echo "Access it at: /admin/promotion\n";
