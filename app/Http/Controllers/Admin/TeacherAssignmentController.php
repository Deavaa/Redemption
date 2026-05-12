<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use App\Models\Subject;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;

class TeacherAssignmentController extends Controller
{
    public function index()
    {
        $assignments = TeacherAssignment::with(['user', 'subject'])
            ->orderBy('subject_id')
            ->orderBy('class_grade')
            ->paginate(20);
        $subjects = Subject::orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        return view('admin.teacher-assignments.index', compact('assignments', 'subjects', 'teachers'));
    }

    public function create()
    {
        $subjects = Subject::orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();
        $classGrades = Student::distinct()->pluck('class_grade')->filter()->sort()->values();
        $sections = ['A', 'B', 'C', 'D'];
        return view('admin.teacher-assignments.create', compact('subjects', 'teachers', 'classGrades', 'sections'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_grade' => 'required|string',
            'section' => 'required|string',
        ]);

        $exists = TeacherAssignment::where('user_id', $request->user_id)
            ->where('subject_id', $request->subject_id)
            ->where('class_grade', $request->class_grade)
            ->where('section', $request->section)
            ->exists();

        if ($exists) {
            return back()->withErrors('This assignment already exists.')->withInput();
        }

        TeacherAssignment::create($request->only('user_id', 'subject_id', 'class_grade', 'section'));
        return redirect()->route('teacher-assignments.index')->with('success', 'Teacher assigned.');
    }

    public function destroy($id)
    {
        TeacherAssignment::destroy($id);
        return redirect()->route('teacher-assignments.index')->with('success', 'Assignment removed.');
    }
}
