<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        $data = Teacher::orderBy('first_name')->paginate(20);
        return view('admin.Teacher.index', compact('data'));
    }

    public function create()
    {
        return view('admin.Teacher.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'nullable|email|max:255|unique:teachers,email',
            'phone'         => 'nullable|string|max:20',
            'qualification' => 'nullable|string|max:255',
            'department'    => 'nullable|string|max:255',
            'hire_date'     => 'nullable|date',
            'salary'        => 'nullable|numeric',
            'status'        => 'nullable|in:Active,Inactive,On Leave',
        ]);

        try {
            $t = Teacher::create($validated);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['id' => $t->id, 'first_name' => $t->first_name, 'last_name' => $t->last_name, 'email' => $t->email ?? '', 'department' => $t->department ?? '']);
            }
            return redirect()->route('admin.teachers.index')->with('success', 'Teacher created successfully.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $data = Teacher::findOrFail($id);
        return view('admin.Teacher.show', compact('data'));
    }

    public function edit($id)
    {
        $data = Teacher::findOrFail($id);
        return view('admin.Teacher.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $item = Teacher::findOrFail($id);

        $validated = $request->validate([
            'first_name'    => 'required|string|max:255',
            'last_name'     => 'required|string|max:255',
            'email'         => 'nullable|email|max:255|unique:teachers,email,' . $id,
            'phone'         => 'nullable|string|max:20',
            'qualification' => 'nullable|string|max:255',
            'department'    => 'nullable|string|max:255',
            'hire_date'     => 'nullable|date',
            'salary'        => 'nullable|numeric',
            'status'        => 'nullable|in:Active,Inactive,On Leave',
        ]);

        $item->update($validated);

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher updated successfully.');
    }

    public function destroy($id)
    {
        Teacher::destroy($id);
        return redirect()->route('admin.teachers.index')->with('success', 'Teacher deleted successfully.');
    }
}
