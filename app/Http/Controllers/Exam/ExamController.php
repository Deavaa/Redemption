<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ExamController extends Controller
{
    public function index()
    {
        $data = Exam::with(['academicYear', 'term'])
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.Exam.index', compact('data'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $allTerms = Term::orderBy('id')->get();

        return view('admin.Exam.create', compact('academicYears', 'allTerms'));
    }

    public function store(Request $r)
    {
        try {
            $r->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:exam,quiz,test,midterm,final,assignment,project',
                'total_marks' => 'required|numeric|min:0|max:99999',
                'academic_year_id' => 'required|exists:academic_years,id',
                'term_id' => 'required|exists:terms,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'start_time' => 'nullable',
                'end_time' => 'nullable',
                'description' => 'nullable|string|max:1000',
            ]);

            $data = [
                'name' => $r->name,
                'type' => $r->type,
                'total_marks' => $r->total_marks,
                'academic_year_id' => $r->academic_year_id,
                'term_id' => $r->term_id,
                'start_date' => $r->start_date,
                'end_date' => $r->end_date,
                'start_time' => $r->filled('start_time') ? $r->start_time . ':00' : null,
                'end_time' => $r->filled('end_time') ? $r->end_time . ':00' : null,
                'description' => $r->description,
            ];

            Log::info('Exam store: attempting create', ['data' => $data]);

            $exam = Exam::create($data);

            Log::info('Exam store: created successfully', ['exam_id' => $exam->id]);

            return redirect()->route('admin.exams.index')
                ->with('success', 'Exam scheduled successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Exam store: validation failed', ['errors' => $e->errors()]);
            throw $e; // Re-throw so Laravel handles the redirect with errors
        } catch (\Exception $e) {
            Log::error('Exam store: exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withInput()
                ->with('error', 'Failed to save exam: ' . $e->getMessage());
        }
    }

    public function show(Exam $exam)
    {
        $exam->load(['academicYear', 'term']);

        return view('admin.Exam.show', compact('exam'));
    }

    public function edit(Exam $exam)
    {
        $exam->load(['academicYear', 'term']);
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $allTerms = Term::orderBy('id')->get();

        return view('admin.Exam.edit', compact('exam', 'academicYears', 'allTerms'));
    }

    public function update(Request $r, Exam $exam)
    {
        try {
            $r->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:exam,quiz,test,midterm,final,assignment,project',
                'total_marks' => 'required|numeric|min:0|max:99999',
                'academic_year_id' => 'required|exists:academic_years,id',
                'term_id' => 'required|exists:terms,id',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'start_time' => 'nullable',
                'end_time' => 'nullable',
                'description' => 'nullable|string|max:1000',
            ]);

            $data = [
                'name' => $r->name,
                'type' => $r->type,
                'total_marks' => $r->total_marks,
                'academic_year_id' => $r->academic_year_id,
                'term_id' => $r->term_id,
                'start_date' => $r->start_date,
                'end_date' => $r->end_date,
                'start_time' => $r->filled('start_time') ? $r->start_time . ':00' : null,
                'end_time' => $r->filled('end_time') ? $r->end_time . ':00' : null,
                'description' => $r->description,
            ];

            $exam->update($data);

            return redirect()->route('admin.exams.index')
                ->with('success', 'Exam updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Exam update: exception', [
                'message' => $e->getMessage(),
                'exam_id' => $exam->id,
            ]);
            return back()->withInput()
                ->with('error', 'Failed to update exam: ' . $e->getMessage());
        }
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return back()->with('success', 'Exam deleted successfully.');
    }
}
