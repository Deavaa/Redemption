<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\AcademicYear;
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
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:exam,quiz,test,midterm,final,assignment,project',
            'total_marks'      => 'required|numeric|min:0|max:99999',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id'          => 'required|exists:terms,id',
            'class_id'         => 'nullable|exists:classes,id',
            'subject_id'       => 'nullable|exists:subjects,id',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i',
            'description'      => 'nullable|string|max:1000',
        ]);

        Exam::create($r->only([
            'name', 'type', 'total_marks',
            'academic_year_id', 'term_id',
            'class_id', 'subject_id',
            'start_date', 'end_date',
            'start_time', 'end_time',
            'description',
        ]));

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam scheduled for all subjects and all classes.');
    }

    public function show(Exam $exam)
    {
        $exam->load(['academicYear', 'term']);

        return view('admin.Exam.show', ['item' => $exam]);
    }

    public function edit(Exam $exam)
    {
        $exam->load(['academicYear', 'term']);
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $allTerms = Term::orderBy('id')->get();

        return view('admin.Exam.edit', ['item' => $exam, 'academicYears' => $academicYears, 'allTerms' => $allTerms]);
    }

    public function update(Request $r, Exam $exam)
    {
        $r->validate([
            'name'             => 'required|string|max:255',
            'type'             => 'required|in:exam,quiz,test,midterm,final,assignment,project',
            'total_marks'      => 'required|numeric|min:0|max:99999',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id'          => 'required|exists:terms,id',
            'class_id'         => 'nullable|exists:classes,id',
            'subject_id'       => 'nullable|exists:subjects,id',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i',
            'description'      => 'nullable|string|max:1000',
        ]);

        $exam->update($r->only([
            'name', 'type', 'total_marks',
            'academic_year_id', 'term_id',
            'class_id', 'subject_id',
            'start_date', 'end_date',
            'start_time', 'end_time',
            'description',
        ]));

        return redirect()->route('admin.exams.index')
            ->with('success', 'Exam updated successfully.');
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();

        return back()->with('success', 'Exam deleted successfully.');
    }
}
