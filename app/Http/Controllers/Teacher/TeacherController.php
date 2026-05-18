<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index()
    {
        $data = Teacher::orderBy('full_name')->paginate(20);
        return view('admin.Teacher.index', compact('data'));
    }

    public function create()
    {
        return view('admin.Teacher.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'     => 'required|string|max:255',
            'email'         => 'nullable|email|max:255|unique:teachers,email',
            'phone'         => 'nullable|string|max:20',
            'qualification' => 'nullable|string|max:255',
            'department'    => 'nullable|string|max:255',
            'hire_date'     => 'nullable|date',
            'salary'        => 'nullable|numeric',
            'status'        => 'nullable|in:active,inactive,on_leave',
            'address'       => 'nullable|string|max:500',
            'photo'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('teacher-photos', 'public');
        }

        // Set default status if not provided
        if (empty($validated['status'])) {
            $validated['status'] = 'active';
        }

        try {
            $t = Teacher::create($validated);
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['id' => $t->id, 'full_name' => $t->full_name, 'email' => $t->email ?? '', 'department' => $t->department ?? '']);
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
            'full_name'     => 'required|string|max:255',
            'email'         => 'nullable|email|max:255|unique:teachers,email,' . $id,
            'phone'         => 'nullable|string|max:20',
            'qualification' => 'nullable|string|max:255',
            'department'    => 'nullable|string|max:255',
            'hire_date'     => 'nullable|date',
            'salary'        => 'nullable|numeric',
            'status'        => 'nullable|in:active,inactive,on_leave',
            'address'       => 'nullable|string|max:500',
            'photo'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('teacher-photos', 'public');
        }

        // Set default status if not provided
        if (empty($validated['status'])) {
            $validated['status'] = 'active';
        }

        $item->update($validated);

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher updated successfully.');
    }

    public function destroy($id)
    {
        Teacher::destroy($id);
        return redirect()->route('admin.teachers.index')->with('success', 'Teacher deleted successfully.');
    }
}
