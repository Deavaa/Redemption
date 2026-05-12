<?php

namespace App\Http\Controllers\MarkEntry;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\MarkEntry;
use App\Models\Section;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use Illuminate\Http\Request;

class MarkEntryController extends Controller
{
    public function index()
    {
        $currentAy = AcademicYear::where('is_current', true)->first() ?? AcademicYear::latest()->first();
        $currentTerm = Term::where('is_active', true)->first() ?? Term::latest()->first();
        $academicYears = AcademicYear::all();
        $terms = Term::all();
        $sections = Section::with('classRoom.branch')->get();

        return view('admin.mark-entries.index', compact('academicYears', 'terms', 'sections', 'currentAy', 'currentTerm'));
    }

    // API methods
    public function apiTerms(Request $request)
    {
        $terms = Term::where('academic_year_id', $request->academic_year_id)->get();

        return response()->json($terms);
    }

    public function apiSections(Request $request)
    {
        $sections = Section::where('class_id', $request->class_id)->get();

        return response()->json($sections);
    }

    public function apiSubjects(Request $request)
    {
        $subjects = Subject::whereHas('assignments', function ($q) use ($request) {
            $q->where('class_id', $request->class_id)->where(function ($sq) use ($request) {
                $sq->whereNull('academic_year_id')->orWhere('academic_year_id', $request->academic_year_id);
            })->where(function ($sq) use ($request) {
                $sq->whereNull('section_id')->orWhere('section_id', $request->section_id);
            });
        })->get();

        return response()->json($subjects);
    }

    public function apiStudents(Request $request)
    {
        $students = Student::where('class_id', $request->class_id)
            ->where('section_id', $request->section_id)
            ->where('academic_year_id', $request->academic_year_id)
            ->with(['markEntries' => function ($q) use ($request) {
                $q->where('subject_id', $request->subject_id)->where('term_id', $request->term_id)->where('academic_year_id', $request->academic_year_id);
            }])->get()->map(function ($student) {
                $marks = $student->markEntries->first();
                $fullName = trim(($student->first_name ?? '').' '.($student->last_name ?? ''));

                return [
                    'id' => $student->id,
                    'name' => $fullName ?: 'Student name',
                    'admission_number' => $student->admission_number ?: 'Auto-generated',
                    'roll_number' => $student->roll_number ?: 'Auto-generated',
                    'marks' => $marks ? $marks->only(['ca1', 'ca2', 'ca3', 'ca4', 'ca5', 'ca6', 'ca7', 'ca8', 'ca9', 'ca10', 'conduct', 'handwriting', 'creativity', 'test1', 'test2', 'mid_term', 'final_exam']) : [],
                ];
            });

        return response()->json($students);
    }

    public function apiSave(Request $request)
    {
        $data = [
            'student_id' => $request->student_id,
            'subject_id' => $request->subject_id,
            'academic_year_id' => $request->academic_year_id,
            'term_id' => $request->term_id,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            $request->mark_key => $request->mark_value,
        ];

        $data = MarkEntry::calcTotals($data);

        $mark = MarkEntry::updateOrCreate([
            'student_id' => $request->student_id,
            'subject_id' => $request->subject_id,
            'academic_year_id' => $request->academic_year_id,
            'term_id' => $request->term_id,
        ], $data);

        return response()->json(['success' => true, 'mark_id' => $mark->id]);
    }

    public function create()
    {
        return view('admin.MarkEntry.create');
    }

    public function store(Request $r)
    {
        MarkEntry::create($r->all());

        return redirect()->route('admin.mark-entries.index')->with('success', 'Created successfully');
    }

    public function show(MarkEntry $item)
    {
        return view('admin.MarkEntry.show', compact('item'));
    }

    public function edit(MarkEntry $item)
    {
        return view('admin.MarkEntry.edit', compact('item'));
    }

    public function update(Request $r, MarkEntry $item)
    {
        $item->update($r->all());

        return redirect()->route('admin.mark-entries.index')->with('success', 'Updated successfully');
    }

    public function destroy(MarkEntry $item)
    {
        $item->delete();

        return back()->with('success', 'Deleted successfully');
    }
}
