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
        $data = Exam::with(['academicYear', 'term'])->latest()->paginate(20);
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
            'type' => 'required|string|max:100',
            'total_marks' => 'required|numeric|min:0',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'description' => 'nullable|string|max:1000',
        ]);

        Exam::create($r->only([
            'name', 'type', 'total_marks',
            'academic_year_id', 'term_id',
            'start_date', 'end_date',
            'start_time', 'end_time',
            'description',
        ]));

        return redirect()->route('admin.exams.index')->with('success', 'Exam scheduled for all subjects and all classes.');
    }

    public function show(Exam $item)
    {
        $item->load(['academicYear', 'term']);
        return view('admin.Exam.show', compact('item'));
    }

    public function edit(Exam $item)
    {
        $academicYears = AcademicYear::orderByDesc('id')->get();
        $allTerms = Term::orderBy('id')->get();
        return view('admin.Exam.edit', compact('item', 'academicYears', 'allTerms'));
    }

    public function update(Request $r, Exam $item)
    {
        $r->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'total_marks' => 'required|numeric|min:0',
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'required|exists:terms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'description' => 'nullable|string|max:1000',
        ]);

        $item->update($r->only([
            'name', 'type', 'total_marks',
            'academic_year_id', 'term_id',
            'start_date', 'end_date',
            'start_time', 'end_time',
            'description',
        ]));

        return redirect()->route('admin.exams.index')->with('success', 'Exam updated successfully.');
    }

    public function destroy(Exam $item)
    {
        $item->delete();
        return back()->with('success', 'Exam deleted successfully.');
    }
}
