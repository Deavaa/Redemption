<?php
namespace App\Http\Controllers\UserAccess;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Role;
use App\Services\EmployeeIdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherAccessController extends Controller
{
    public function index()
    {
        $teachers = Teacher::with('user')->orderBy('full_name')->get();
        $teacherRole = Role::where('name', 'teacher')->first();
        return view('admin.user-access.teachers', compact('teachers', 'teacherRole'));
    }

    public function createAccount(Request $request)
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        $teacher = Teacher::findOrFail($request->teacher_id);
        $employeeIdService = new EmployeeIdService();
        $defaultPassword = $employeeIdService->getDefaultPassword();

        // Check if user already exists
        if ($teacher->user_id) {
            return redirect()->back()->with('error', 'This teacher already has a user account.');
        }

        // Check by email
        $existingUser = User::where('email', $teacher->email)->first();
        if ($existingUser) {
            // Link existing user to teacher
            $teacher->update(['user_id' => $existingUser->id]);
            // Assign teacher role
            $teacherRole = Role::firstOrCreate(['name' => 'teacher'], ['display_name' => 'Teacher', 'description' => 'Teacher with class-specific access']);
            $existingUser->roles()->syncWithoutDetaching([$teacherRole->id]);
            // Update legacy role column
            $existingUser->update(['role' => 'teacher']);
            // Assign employee ID if missing
            $employeeIdService->assignToUser($existingUser, $teacher->branch_id);
            return redirect()->back()->with('success', 'Existing user linked to teacher successfully.');
        }

        // Normalize phone number
        $phone = $teacher->phone;
        if ($phone) {
            $phone = $this->normalizePhone($phone);
        }

        // Create new user account with auto-generated employee ID and default password
        $user = User::create([
            'name' => $teacher->full_name,
            'email' => $teacher->email,
            'password' => Hash::make($defaultPassword),
            'must_change_password' => true,
            'role' => 'teacher',
            'phone' => $phone,
            'branch_id' => $teacher->branch_id,
            'is_active' => true,
        ]);

        // Auto-generate employee ID
        $employeeId = $employeeIdService->assignToUser($user, $teacher->branch_id);

        // Assign teacher role
        $teacherRole = Role::firstOrCreate(['name' => 'teacher'], ['display_name' => 'Teacher', 'description' => 'Teacher with class-specific access']);
        $user->roles()->syncWithoutDetaching([$teacherRole->id]);

        // Link to teacher
        $teacher->update(['user_id' => $user->id]);

        return redirect()->back()->with('success', "Teacher account created. Employee ID: {$employeeId}. Default password: {$defaultPassword}");
    }

    public function assignPermissions(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permissions' => 'array',
        ]);

        $user = User::findOrFail($request->user_id);
        $permissionIds = $request->input('permissions', []);

        // Sync direct permissions
        $user->directPermissions()->sync($permissionIds);

        return redirect()->back()->with('success', 'Permissions updated successfully.');
    }

    /**
     * Reset a user's password to the default password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $defaultPassword = (new EmployeeIdService())->getDefaultPassword();
        $user->update(['password' => Hash::make($defaultPassword)]);
'must_change_password' => true,

        return redirect()->back()->with('success', "Password reset to default: {$defaultPassword}");
    }

    /**
     * Normalize phone number to 0900000000 format.
     */
    private function normalizePhone(string $input): string
    {
        $cleaned = preg_replace('/[\s\-().]/', '', $input);
        if (preg_match('/^(\+251|00251)(\d{9})$/', $cleaned, $m)) {
            return '0' . $m[2];
        }
        if (preg_match('/^251(\d{9})$/', $cleaned, $m)) {
            return '0' . $m[1];
        }
        if (preg_match('/^0\d{9}$/', $cleaned)) {
            return $cleaned;
        }
        return $cleaned;
    }
}
