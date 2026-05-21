<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Exam;
use App\Models\Term;
use Illuminate\Http\Request;

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
        $r->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:exam,quiz,test,midterm,final,assignment,project',
            'total_marks' => 'required|numeric|min:0|max:99999',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'description' => 'nullable|string|max:1000',
        ]);

        // HTML <input type="time"> sends "HH:MM" but DB expects "HH:MM:SS"
        $data = $r->only([
            'name', 'type', 'total_marks',
            'academic_year_id', 'term_id',
            'start_date', 'end_date',
            'description',
        ]);
        $data['start_time'] = $r->filled('start_time') ? $r->start_time . ':00' : null;
        $data['end_time'] = $r->filled('end_time') ? $r->end_time . ':00' : null;

        Exam::create($data);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam scheduled successfully.');
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
        $r->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:exam,quiz,test,midterm,final,assignment,project',
            'total_marks' => 'required|numeric|min:0|max:99999',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'description' => 'nullable|string|max:1000',
        ]);

        $data = $r->only([
            'name', 'type', 'total_marks',
            'academic_year_id', 'term_id',
            'start_date', 'end_date',
            'description',
        ]);
        $data['start_time'] = $r->filled('start_time') ? $r->start_time . ':00' : null;
        $data['end_time'] = $r->filled('end_time') ? $r->end_time . ':00' : null;

        $exam->update($data);

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return back()->with('success', 'Exam deleted successfully.');
    }
}
