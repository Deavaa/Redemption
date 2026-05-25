<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Department;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{
    public function index()
    {
        $branchScope = request()->attributes->get('branch_scope');
        
        $query = Teacher::orderBy('full_name');
        
        // Branch principal: only see teachers in their branch
        if ($branchScope) {
            $query->where(function ($q) use ($branchScope) {
                $q->where('branch_id', $branchScope)
                  ->orWhereHas('assignments', function ($aq) use ($branchScope) {
                      $aq->whereHas('classroom', function ($cq) use ($branchScope) {
                          $cq->where('branch_id', $branchScope);
                      });
                  })
                  ->orWhereHas('sections', function ($sq) use ($branchScope) {
                      $sq->whereHas('classroom', function ($cq) use ($branchScope) {
                          $cq->where('branch_id', $branchScope);
                      });
                  });
            });
        }
        
        $data = $query->paginate(10)->withQueryString();
        return view('admin.Teacher.index', compact('data'));
    }

    public function create()
    {
        $departments = Department::active()->orderBy('name')->get();
        return view('admin.Teacher.create', compact('departments'));
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
            'department_id' => 'nullable|exists:departments,id',
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
        $data = Teacher::with('department')->findOrFail($id);
        return view('admin.Teacher.show', compact('data'));
    }

    public function edit($id)
    {
        $data = Teacher::findOrFail($id);
        $departments = Department::active()->orderBy('name')->get();
        return view('admin.Teacher.edit', compact('data', 'departments'));
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
            'department_id' => 'nullable|exists:departments,id',
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

        $page = $request->input('page', 1);
        return redirect()->route('admin.teachers.index', ['page' => $page])->with('success', 'Teacher updated successfully.');
    }

    public function destroy($id)
    {
        Teacher::destroy($id);
        return redirect()->route('admin.teachers.index')->with('success', 'Teacher deleted successfully.');
    }

    /**
     * Show the transfer form for a teacher (move to another branch).
     */
    public function transferForm(Teacher $teacher)
    {
        $teacher->load(['branch']);
        $branches = Branch::where('id', '!=', $teacher->branch_id)->where('is_active', true)->get();

        return view('admin.Teacher.transfer', compact('teacher', 'branches'));
    }

    /**
     * Transfer a teacher to another branch.
     */
    public function transfer(Request $request, Teacher $teacher)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id|different:' . $teacher->branch_id,
            'transfer_reason' => 'nullable|string|max:500',
        ]);

        $oldBranch = $teacher->branch ? $teacher->branch->name : 'Unknown';
        $newBranch = Branch::find($validated['branch_id'])->name;

        $teacher->update([
            'branch_id' => $validated['branch_id'],
            'previous_branch_id' => $teacher->branch_id,
        ]);

        // Update the user's branch_id too if they have a user account
        if ($teacher->user_id) {
            \App\Models\User::where('id', $teacher->user_id)->update(['branch_id' => $validated['branch_id']]);
        }

        // Notify relevant users about the transfer
        try {
            \App\Services\AlertService::notifyTeacherTransfer(
                $teacher->previous_branch_id ?? $validated['branch_id'],
                $validated['branch_id'],
                $teacher->full_name
            );
        } catch (\Exception $e) {
            \Log::warning('Teacher transfer notification failed: ' . $e->getMessage());
        }

        return redirect()->route('admin.teachers.index')
            ->with('success', "Teacher transferred from {$oldBranch} to {$newBranch} successfully!");
    }
}
