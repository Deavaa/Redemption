<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        // Debug: log raw request data to trace the status value
        Log::info('Teacher STORE - Raw request status', [
            'status' => $request->input('status'),
            'all' => $request->except('photo'),
        ]);

        $validated = $request->validate([
            'full_name'     => 'required|string|max:255',
            'email'         => 'nullable|email|max:255|unique:teachers,email',
            'phone'         => 'nullable|string|max:20',
            'qualification' => 'nullable|string|max:255',
            'department'    => 'nullable|string|max:255',
            'hire_date'     => 'nullable|date',
            'salary'        => 'nullable|numeric',
            'status'        => 'required|in:active,inactive,on_leave',
            'address'       => 'nullable|string|max:500',
            'photo'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Debug: log validated data
        Log::info('Teacher STORE - Validated data', $validated);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('teacher-photos', 'public');
        }

        // Handle NOT NULL columns: set defaults when values are empty/null
        if (empty($validated['salary'])) {
            $validated['salary'] = 0;
        }
        if (empty($validated['email'])) {
            $validated['email'] = '';
        }

        // Ensure status is explicitly set (defensive coding)
        if (!isset($validated['status']) || !in_array($validated['status'], ['active', 'inactive', 'on_leave'])) {
            $validated['status'] = 'active';
        }

        try {
            $t = Teacher::create($validated);

            // Debug: log what was actually saved
            Log::info('Teacher STORE - After create', [
                'id' => $t->id,
                'status_in_validated' => $validated['status'],
                'status_from_model' => $t->status,
                'status_from_db' => Teacher::find($t->id)?->status,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['id' => $t->id, 'full_name' => $t->full_name, 'email' => $t->email ?? '', 'department' => $t->department ?? '']);
            }
            return redirect()->route('admin.teachers.index')->with('success', 'Teacher created successfully.');
        } catch (\Exception $e) {
            Log::error('Teacher STORE - Exception', ['message' => $e->getMessage()]);
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

        // Debug: log raw request data
        Log::info('Teacher UPDATE - Raw request status', [
            'id' => $id,
            'status' => $request->input('status'),
            'all' => $request->except('photo'),
        ]);

        $validated = $request->validate([
            'full_name'     => 'required|string|max:255',
            'email'         => 'nullable|email|max:255|unique:teachers,email,' . $id,
            'phone'         => 'nullable|string|max:20',
            'qualification' => 'nullable|string|max:255',
            'department'    => 'nullable|string|max:255',
            'hire_date'     => 'nullable|date',
            'salary'        => 'nullable|numeric',
            'status'        => 'required|in:active,inactive,on_leave',
            'address'       => 'nullable|string|max:500',
            'photo'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Debug: log validated data
        Log::info('Teacher UPDATE - Validated data', $validated);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('teacher-photos', 'public');
        }

        // Handle NOT NULL columns: set defaults when values are empty/null
        if (empty($validated['salary'])) {
            $validated['salary'] = 0;
        }
        if (empty($validated['email'])) {
            $validated['email'] = '';
        }

        // Ensure status is explicitly set (defensive coding)
        if (!isset($validated['status']) || !in_array($validated['status'], ['active', 'inactive', 'on_leave'])) {
            $validated['status'] = 'active';
        }

        $item->update($validated);

        // Debug: log what was actually saved
        Log::info('Teacher UPDATE - After update', [
            'id' => $id,
            'status_in_validated' => $validated['status'],
            'status_from_model' => $item->fresh()->status,
            'status_from_db' => Teacher::find($id)?->status,
        ]);

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher updated successfully.');
    }

    public function destroy($id)
    {
        Teacher::destroy($id);
        return redirect()->route('admin.teachers.index')->with('success', 'Teacher deleted successfully.');
    }
}
