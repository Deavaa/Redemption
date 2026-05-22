<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::with('headUser', 'teachers')->orderBy('name')->paginate(15);
        return view('admin.Departments.index', compact('departments'));
    }

    public function create()
    {
        $departmentHeads = User::where('role', 'department_head')->orderBy('name')->get();
        return view('admin.Departments.create', compact('departmentHeads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'code' => 'nullable|string|max:20',
            'type' => 'required|in:academic,administrative,support',
            'description' => 'nullable|string',
            'head_user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        Department::create($validated);

        return redirect()->route('admin.departments.index')->with('success', 'Department created successfully.');
    }

    public function show(Department $department)
    {
        $department->load('headUser', 'teachers');
        return view('admin.Departments.show', compact('department'));
    }

    public function edit(Department $department)
    {
        $departmentHeads = User::where('role', 'department_head')->orderBy('name')->get();
        return view('admin.Departments.edit', compact('department', 'departmentHeads'));
    }

    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
            'code' => 'nullable|string|max:20',
            'type' => 'required|in:academic,administrative,support',
            'description' => 'nullable|string',
            'head_user_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('admin.departments.index')->with('success', 'Department deleted.');
    }
}
