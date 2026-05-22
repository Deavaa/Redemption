<?php

namespace App\Http\Controllers\TeacherEvaluation;

use App\Http\Controllers\Controller;
use App\Models\TeacherEvaluation;
use App\Models\Teacher;
use App\Models\Department;
use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherEvaluationController extends Controller
{
    public function index(Request $request)
    {
        $query = TeacherEvaluation::with(['teacher', 'evaluator', 'academicYear', 'term']);

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }
        if ($request->filled('department_id')) {
            $dept = Department::find($request->department_id);
            if ($dept) {
                $teacherIds = $dept->teachers()->pluck('teachers.id');
                $query->whereIn('teacher_id', $teacherIds);
            }
        }
        if ($request->filled('grade')) {
            $query->where('grade', $request->grade);
        }

        $evaluations = $query->latestFirst()->paginate(15)->withQueryString();
        $teachers = Teacher::orderBy('full_name')->get();
        $departments = Department::active()->orderBy('name')->get();

        // Stats
        $allEvals = TeacherEvaluation::completed()->get();
        $stats = [
            'total' => $allEvals->count(),
            'excellent' => $allEvals->where('grade', 'excellent')->count(),
            'good' => $allEvals->where('grade', 'good')->count(),
            'satisfactory' => $allEvals->where('grade', 'satisfactory')->count(),
            'needs_improvement' => $allEvals->where('grade', 'needs_improvement')->count(),
            'unsatisfactory' => $allEvals->where('grade', 'unsatisfactory')->count(),
            'avg_score' => $allEvals->avg('overall_score') ? round($allEvals->avg('overall_score'), 1) : 0,
        ];

        return view('admin.TeacherEvaluation.index', compact('evaluations', 'teachers', 'departments', 'stats'));
    }

    public function create()
    {
        $teachers = Teacher::where('status', 'active')->orderBy('full_name')->get();
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $allTerms = Term::orderBy('id')->get();
        $departments = Department::active()->orderBy('name')->get();

        return view('admin.TeacherEvaluation.create', compact('teachers', 'academicYears', 'allTerms', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'evaluation_type' => 'required|in:periodic,annual,observation,peer_review',
            'evaluation_date' => 'required|date',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'teaching_quality' => 'required|integer|min:0|max:5',
            'student_engagement' => 'required|integer|min:0|max:5',
            'classroom_management' => 'required|integer|min:0|max:5',
            'lesson_preparation' => 'required|integer|min:0|max:5',
            'professional_conduct' => 'required|integer|min:0|max:5',
            'communication_skills' => 'required|integer|min:0|max:5',
            'punctuality' => 'required|integer|min:0|max:5',
            'student_results' => 'required|integer|min:0|max:5',
            'strengths' => 'nullable|string',
            'areas_for_improvement' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'comments' => 'nullable|string',
        ]);

        $validated['evaluator_id'] = Auth::id();
        $validated['overall_score'] = TeacherEvaluation::calculateOverallScore($validated);
        $validated['grade'] = TeacherEvaluation::scoreToGrade($validated['overall_score']);
        $validated['status'] = 'completed';

        TeacherEvaluation::create($validated);

        return redirect()->route('admin.teacher-evaluations.index')->with('success', 'Teacher evaluation recorded successfully.');
    }

    public function show(TeacherEvaluation $teacherEvaluation)
    {
        $teacherEvaluation->load(['teacher', 'evaluator', 'academicYear', 'term']);
        return view('admin.TeacherEvaluation.show', compact('teacherEvaluation'));
    }

    public function edit(TeacherEvaluation $teacherEvaluation)
    {
        $teachers = Teacher::where('status', 'active')->orderBy('full_name')->get();
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $allTerms = Term::orderBy('id')->get();

        return view('admin.TeacherEvaluation.edit', compact('teacherEvaluation', 'teachers', 'academicYears', 'allTerms'));
    }

    public function update(Request $request, TeacherEvaluation $teacherEvaluation)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'evaluation_type' => 'required|in:periodic,annual,observation,peer_review',
            'evaluation_date' => 'required|date',
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'teaching_quality' => 'required|integer|min:0|max:5',
            'student_engagement' => 'required|integer|min:0|max:5',
            'classroom_management' => 'required|integer|min:0|max:5',
            'lesson_preparation' => 'required|integer|min:0|max:5',
            'professional_conduct' => 'required|integer|min:0|max:5',
            'communication_skills' => 'required|integer|min:0|max:5',
            'punctuality' => 'required|integer|min:0|max:5',
            'student_results' => 'required|integer|min:0|max:5',
            'strengths' => 'nullable|string',
            'areas_for_improvement' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'comments' => 'nullable|string',
        ]);

        $validated['overall_score'] = TeacherEvaluation::calculateOverallScore($validated);
        $validated['grade'] = TeacherEvaluation::scoreToGrade($validated['overall_score']);

        $teacherEvaluation->update($validated);

        return redirect()->route('admin.teacher-evaluations.index')->with('success', 'Evaluation updated successfully.');
    }

    public function destroy(TeacherEvaluation $teacherEvaluation)
    {
        $teacherEvaluation->delete();
        return redirect()->route('admin.teacher-evaluations.index')->with('success', 'Evaluation deleted.');
    }

    // Teacher analysis page
    public function analysis(Request $request)
    {
        $teacherId = $request->get('teacher_id');
        $departmentId = $request->get('department_id');

        $teachers = Teacher::where('status', 'active')->orderBy('full_name')->get();
        $departments = Department::active()->orderBy('name')->get();

        $selectedTeacher = $teacherId ? Teacher::find($teacherId) : null;
        $evaluations = collect();
        $trendData = collect();

        if ($selectedTeacher) {
            $evaluations = TeacherEvaluation::where('teacher_id', $selectedTeacher->id)
                ->with(['evaluator', 'academicYear', 'term'])
                ->latestFirst()
                ->get();

            $trendData = $evaluations->take(10)->reverse()->values();
        }

        // Department summary
        $deptStats = collect();
        if ($departmentId) {
            $dept = Department::find($departmentId);
            if ($dept) {
                $teacherIds = $dept->teachers()->pluck('teachers.id');
                $deptEvals = TeacherEvaluation::whereIn('teacher_id', $teacherIds)->completed()->get();
                $deptStats = [
                    'department' => $dept->name,
                    'total_evaluations' => $deptEvals->count(),
                    'avg_score' => $deptEvals->avg('overall_score') ? round($deptEvals->avg('overall_score'), 1) : 0,
                    'excellent' => $deptEvals->where('grade', 'excellent')->count(),
                    'good' => $deptEvals->where('grade', 'good')->count(),
                    'satisfactory' => $deptEvals->where('grade', 'satisfactory')->count(),
                    'needs_improvement' => $deptEvals->where('grade', 'needs_improvement')->count(),
                    'unsatisfactory' => $deptEvals->where('grade', 'unsatisfactory')->count(),
                    'teachers' => $dept->teachers()->where('status', 'active')->get()->map(function ($t) use ($deptEvals) {
                        $tEvals = $deptEvals->where('teacher_id', $t->id);
                        return [
                            'id' => $t->id,
                            'name' => $t->full_name,
                            'eval_count' => $tEvals->count(),
                            'avg_score' => $tEvals->avg('overall_score') ? round($tEvals->avg('overall_score'), 1) : 0,
                            'latest_grade' => $tEvals->sortByDesc('evaluation_date')->first()?->grade ?? 'N/A',
                        ];
                    }),
                ];
            }
        }

        return view('admin.TeacherEvaluation.analysis', compact(
            'teachers', 'departments', 'selectedTeacher', 'evaluations', 'trendData', 'deptStats', 'teacherId', 'departmentId'
        ));
    }
}
