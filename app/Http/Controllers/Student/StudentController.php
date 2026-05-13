<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\Classroom;
use App\Models\ParentModel;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['classroom', 'section']);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('roll_number', 'LIKE', "%{$search}%");
            });
        }
        $students = $query->latest()->paginate(20);
        $totalStudents = Student::count();
        $activeStudents = Student::where('status', 'active')->count();
        $inactiveStudents = Student::where('status', 'inactive')->count();

        return view('admin.Student.index', compact('students', 'totalStudents', 'activeStudents', 'inactiveStudents'));
    }

    public function create()
    {
        $classrooms = Classroom::with('sections', 'branch')->get();
        $parents = ParentModel::orderBy('father_name')->get(['id', \DB::raw('father_name as name'), \DB::raw('father_phone as phone')]);
        $branches = Branch::all();
        $academicYears = AcademicYear::all();
        if ($academicYears->isEmpty()) {
            AcademicYear::create(['name' => '2024-2025']);
            AcademicYear::create(['name' => '2025-2026']);
            $academicYears = AcademicYear::all();
        }

        return view('admin.Student.create', compact('classrooms', 'parents', 'branches', 'academicYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:students,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'section_id' => 'required|exists:sections,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'admission_number' => 'nullable|string|max:50|unique:students,admission_number',
            'roll_number' => 'nullable|string|max:50',
            'admission_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,graduated,transferred',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('student-photos', 'public');
            $validated['photo'] = $photoPath;
        }

        // Split full name into first and last name
        $nameParts = explode(' ', $validated['full_name'], 2);
        $validated['first_name'] = $nameParts[0] ?? '';
        $validated['last_name'] = $nameParts[1] ?? '';
        unset($validated['full_name']);

        // Get class_id from section_id
        $section = Section::find($validated['section_id']);
        $validated['class_id'] = $section->class_id;

        // Generate admission_number if not provided
        if (empty($validated['admission_number'])) {
            $year = date('Y');
            $lastAdmission = Student::where('admission_number', 'like', $year.'-%')->orderBy('admission_number', 'desc')->first();
            $nextNum = $lastAdmission ? ((int) substr($lastAdmission->admission_number, -4)) + 1 : 1;
            $validated['admission_number'] = $year.'-'.str_pad($nextNum, 4, '0', STR_PAD_LEFT);
        }

        // Generate roll_number if not provided
        if (empty($validated['roll_number'])) {
            $maxRoll = Student::where('section_id', $validated['section_id'])->max('roll_number') ?? 0;
            $validated['roll_number'] = $maxRoll + 1;
        }

        $validated['user_id'] = auth()->id();

        Student::create($validated);

        return redirect()->route('admin.students.index')->with('success', 'Student enrolled successfully!');
    }

    public function show(Student $student)
    {
        $student->load(['classroom', 'section', 'branch', 'academicYear', 'parents']);
        return view('admin.Student.show', ['data' => $student]);
    }

    public function edit(Student $student)
    {
        $classrooms = Classroom::with('sections', 'branch')->get();
        $parents = ParentModel::orderBy('father_name')->get(['id', \DB::raw('father_name as name'), \DB::raw('father_phone as phone')]);
        $branches = Branch::all();
        $academicYears = AcademicYear::all();
        $sections = Section::all();
        if ($academicYears->isEmpty()) {
            AcademicYear::create(['name' => '2024-2025']);
            AcademicYear::create(['name' => '2025-2026']);
            $academicYears = AcademicYear::all();
        }

        return view('admin.Student.edit', [
            'data' => $student,
            'classrooms' => $classrooms,
            'parents' => $parents,
            'branches' => $branches,
            'academicYears' => $academicYears,
            'sections' => $sections,
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:students,email,'.$student->id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'branch_id' => 'required|exists:branches,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'section_id' => 'required|exists:sections,id',
            'admission_number' => 'required|string|max:50|unique:students,admission_number,'.$student->id,
            'admission_date' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,graduated,transferred',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('student-photos', 'public');
            $validated['photo'] = $photoPath;
        }

        // Split full name into first and last name
        $nameParts = explode(' ', $validated['full_name'], 2);
        $validated['first_name'] = $nameParts[0] ?? '';
        $validated['last_name'] = $nameParts[1] ?? '';
        unset($validated['full_name']);

        // Get class_id from section_id
        $section = Section::find($validated['section_id']);
        $validated['class_id'] = $section->class_id;

        $student->update($validated);

        return redirect()->route('admin.students.index')->with('success', 'Student updated successfully!');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('admin.students.index')->with('success', 'Student deleted successfully!');
    }
}
