<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Department;
use App\Models\Branch;
use App\Services\TeacherIdService;
use App\Services\EmployeeIdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TeacherController extends Controller
{
    public function index()
    {
        $branchScope = request()->attributes->get('branch_scope');
        
        $query = Teacher::with('branch')->orderBy('full_name');
        
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
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $authUser = auth()->user();
        $isBranchPrincipal = $authUser->role === 'branch_principal';
        $authBranchId = $isBranchPrincipal ? $authUser->branch_id : null;

        // Pre-generate the next teacher ID number for preview
        $teacherIdService = new TeacherIdService();
        $nextTeacherId = $teacherIdService->generate($isBranchPrincipal ? $authBranchId : null);

        return view('admin.Teacher.create', compact('departments', 'branches', 'isBranchPrincipal', 'authBranchId', 'nextTeacherId'));
    }

    public function store(Request $request)
    {
        // Debug: log raw request data to trace the status value
        Log::info('Teacher STORE - Raw request status', [
            'status' => $request->input('status'),
            'all' => $request->except('photo'),
        ]);

        $validated = $request->validate([
            'full_name'          => 'required|string|max:255',
            'teacher_id_number'  => 'nullable|string|max:50|unique:teachers,teacher_id_number',
            'email'              => 'nullable|email|max:255|unique:teachers,email',
            'phone'              => 'required|string|max:20',
            'gender'             => 'nullable|in:male,female',
            'qualification'      => 'nullable|string|max:255',
            'department'         => 'nullable|string|max:255',
            'department_id'      => 'nullable|exists:departments,id',
            'branch_id'          => 'required|exists:branches,id',
            'hire_date'          => 'nullable|date',
            'salary'             => 'nullable|numeric',
            'status'             => 'required|in:active,inactive,on_leave',
            'address'            => 'nullable|string|max:500',
            'photo'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
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
            $validated['email'] = null;
        }

        // Auto-generate teacher_id_number if not provided
        if (empty($validated['teacher_id_number'])) {
            unset($validated['teacher_id_number']); // Let the service generate it after creation
        }

        // Ensure status is explicitly set (defensive coding)
        if (!isset($validated['status']) || !in_array($validated['status'], ['active', 'inactive', 'on_leave'])) {
            $validated['status'] = 'active';
        }

        // Auto-assign branch for branch principal
        if (auth()->user()->role === 'branch_principal') {
            $validated['branch_id'] = auth()->user()->branch_id;
        }

        try {
            // ── Create corresponding User account with role=teacher ──
            $employeeIdService = new EmployeeIdService();
            $defaultPassword = $employeeIdService->getDefaultPassword();

            // Generate a unique email if not provided (required for users table)
            $teacherEmail = $validated['email'] ?? null;
            if (empty($teacherEmail)) {
                // Generate a placeholder email based on name and timestamp
                $slug = \Illuminate\Support\Str::slug($validated['full_name'], '');
                $teacherEmail = $slug . '_' . time() . '@school.local';
            }

            // Generate employee ID for the user account
            $employeeId = $employeeIdService->generate($validated['branch_id'] ?? null);

            $user = User::create([
                'name'         => $validated['full_name'],
                'email'        => $teacherEmail,
                'password'     => Hash::make($defaultPassword),
                'role'         => 'teacher',
                'branch_id'    => $validated['branch_id'] ?? null,
                'phone'        => $validated['phone'] ?? null,
                'gender'       => $validated['gender'] ?? null,
                'qualification'=> $validated['qualification'] ?? null,
                'address'      => $validated['address'] ?? null,
                'employee_id'  => $employeeId,
                'is_active'    => $validated['status'] === 'active',
            ]);

            // Assign RBAC role if it exists
            try {
                $rbacRole = \App\Models\Role::where('name', 'teacher')->first();
                if ($rbacRole) {
                    $user->roles()->sync([$rbacRole->id]);
                }
            } catch (\Throwable $e) {}

            // Link the user account to the teacher record
            $validated['user_id'] = $user->id;
            $validated['email'] = $teacherEmail;

            $t = Teacher::create($validated);

            // Auto-generate teacher ID number if not set
            if (empty($t->teacher_id_number)) {
                $teacherIdService = new TeacherIdService();
                $teacherIdService->assignToTeacher($t, $t->branch_id);
                $t->refresh();
            }

            // Debug: log what was actually saved
            Log::info('Teacher STORE - After create', [
                'id' => $t->id,
                'user_id' => $user->id,
                'teacher_id_number' => $t->teacher_id_number,
                'employee_id' => $employeeId,
                'default_password' => $defaultPassword,
                'status_in_validated' => $validated['status'],
                'status_from_model' => $t->status,
                'status_from_db' => Teacher::find($t->id)?->status,
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['id' => $t->id, 'full_name' => $t->full_name, 'email' => $t->email ?? '', 'department' => $t->department ?? '']);
            }
            return redirect()->route('admin.teachers.index')
                ->with('success', "Teacher created successfully. Employee ID: {$employeeId}. Default password: {$defaultPassword}");
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
        $data = Teacher::with('department', 'branch')->findOrFail($id);
        return view('admin.Teacher.show', compact('data'));
    }

    public function edit($id)
    {
        $data = Teacher::findOrFail($id);
        $departments = Department::active()->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $authUser = auth()->user();
        $isBranchPrincipal = $authUser->role === 'branch_principal';
        $authBranchId = $isBranchPrincipal ? $authUser->branch_id : null;

        return view('admin.Teacher.edit', compact('data', 'departments', 'branches', 'isBranchPrincipal', 'authBranchId'));
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
            'phone'         => 'required|string|max:20',
            'gender'        => 'nullable|in:male,female',
            'qualification' => 'nullable|string|max:255',
            'department'    => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'branch_id'     => 'required|exists:branches,id',
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
            $validated['email'] = null;
        }

        // Ensure status is explicitly set (defensive coding)
        if (!isset($validated['status']) || !in_array($validated['status'], ['active', 'inactive', 'on_leave'])) {
            $validated['status'] = 'active';
        }

        // Auto-assign branch for branch principal (prevent changing away from own branch)
        if (auth()->user()->role === 'branch_principal') {
            $validated['branch_id'] = auth()->user()->branch_id;
        }

        $item->update($validated);

        // ── Sync the linked User account ──
        if ($item->user_id) {
            $linkedUser = User::find($item->user_id);
            if ($linkedUser) {
                $userData = [
                    'name'         => $validated['full_name'],
                    'phone'        => $validated['phone'] ?? null,
                    'gender'       => $validated['gender'] ?? null,
                    'qualification'=> $validated['qualification'] ?? null,
                    'address'      => $validated['address'] ?? null,
                    'branch_id'    => $validated['branch_id'] ?? null,
                    'is_active'    => ($validated['status'] ?? 'active') === 'active',
                ];
                // Only update email if a real one was provided
                if (!empty($validated['email']) && !str_ends_with($validated['email'], '@school.local')) {
                    $userData['email'] = $validated['email'];
                }
                $linkedUser->update($userData);
            }
        } else {
            // Teacher has no linked user account — create one now (backfill)
            try {
                $employeeIdService = new EmployeeIdService();
                $defaultPassword = $employeeIdService->getDefaultPassword();
                $teacherEmail = $validated['email'] ?? null;
                if (empty($teacherEmail)) {
                    $slug = \Illuminate\Support\Str::slug($validated['full_name'], '');
                    $teacherEmail = $slug . '_' . time() . '@school.local';
                }
                $employeeId = $employeeIdService->generate($validated['branch_id'] ?? null);

                $newUser = User::create([
                    'name'         => $validated['full_name'],
                    'email'        => $teacherEmail,
                    'password'     => Hash::make($defaultPassword),
                    'role'         => 'teacher',
                    'branch_id'    => $validated['branch_id'] ?? null,
                    'phone'        => $validated['phone'] ?? null,
                    'gender'       => $validated['gender'] ?? null,
                    'qualification'=> $validated['qualification'] ?? null,
                    'address'      => $validated['address'] ?? null,
                    'employee_id'  => $employeeId,
                    'is_active'    => ($validated['status'] ?? 'active') === 'active',
                ]);

                // Assign RBAC role
                try {
                    $rbacRole = \App\Models\Role::where('name', 'teacher')->first();
                    if ($rbacRole) {
                        $newUser->roles()->sync([$rbacRole->id]);
                    }
                } catch (\Throwable $e) {}

                // Link user to teacher
                $item->update(['user_id' => $newUser->id, 'email' => $teacherEmail]);
            } catch (\Throwable $e) {
                Log::warning('Teacher UPDATE - Failed to create linked user', ['message' => $e->getMessage()]);
            }
        }

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
        $teacher = Teacher::find($id);
        if ($teacher) {
            // Also delete the linked user account (if it was auto-created for this teacher)
            if ($teacher->user_id) {
                $user = User::find($teacher->user_id);
                if ($user && $user->role === 'teacher') {
                    $user->delete();
                }
            }
            $teacher->delete();
        }
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

    /**
     * Export teachers as CSV, filtered by branch / department / status.
     */
    public function exportCsv(Request $request)
    {
        $branchId = $request->query('branch_id');
        $departmentId = $request->query('department_id');
        $status = $request->query('status', 'active');

        $query = Teacher::query();
        if ($branchId) $query->where('branch_id', $branchId);
        if ($departmentId) $query->where('department_id', $departmentId);
        if ($status && $status !== 'all') $query->where('status', $status);

        $teachers = $query->orderBy('full_name')->get();

        $headers = ['id', 'full_name', 'teacher_id_number', 'email', 'phone', 'gender', 'qualification', 'department', 'department_id', 'branch_id', 'hire_date', 'salary', 'status', 'address'];

        $filename = 'teachers_export_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($headers, $teachers) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $headers);
            foreach ($teachers as $t) {
                $row = [];
                foreach ($headers as $h) {
                    $row[] = $t->$h ?? '';
                }
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Import teachers from CSV. Updates existing (by id or teacher_id_number) or creates new.
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('file');
        $handle = fopen($file->getRealPath(), 'r');
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") fseek($handle, 0);

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return back()->with('error', 'CSV file is empty or invalid.');
        }
        $headers = array_map('trim', $headers);

        $allowedFields = ['full_name', 'teacher_id_number', 'email', 'phone', 'gender', 'qualification', 'department', 'department_id', 'branch_id', 'hire_date', 'salary', 'status', 'address'];

        $saved = 0; $updated = 0; $errors = []; $lineNum = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $lineNum++;
            $data = [];
            $id = null; $tidNum = null;
            foreach ($headers as $i => $h) {
                $val = isset($row[$i]) ? trim($row[$i]) : '';
                if ($h === 'id') { $id = $val; continue; }
                if ($h === 'teacher_id_number') { $tidNum = $val; }
                if (in_array($h, $allowedFields)) {
                    $data[$h] = $val !== '' ? $val : null;
                }
            }
            if (empty($data['full_name'])) {
                $errors[] = "Line $lineNum: missing full_name — skipped.";
                continue;
            }
            try {
                $existing = null;
                if ($id) $existing = Teacher::find($id);
                if (!$existing && $tidNum) $existing = Teacher::where('teacher_id_number', $tidNum)->first();
                if ($existing) {
                    $existing->update(array_filter($data, fn($v) => $v !== null));
                    $updated++;
                } else {
                    Teacher::create($data);
                    $saved++;
                }
            } catch (\Throwable $e) {
                $errors[] = "Line $lineNum: " . $e->getMessage();
            }
        }
        fclose($handle);

        $msg = "Imported $saved new teachers, updated $updated existing.";
        if (count($errors) > 0) {
            $msg .= " " . count($errors) . " errors: " . implode(' | ', array_slice($errors, 0, 5));
        }
        return back()->with('success', $msg);
    }
}
