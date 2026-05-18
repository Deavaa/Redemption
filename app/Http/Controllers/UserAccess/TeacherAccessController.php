<?php
namespace App\Http\Controllers\UserAccess;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            return redirect()->back()->with('success', 'Existing user linked to teacher successfully.');
        }

        // Create new user account
        $tempPassword = Str::random(10);
        $user = User::create([
            'name' => $teacher->full_name,
            'email' => $teacher->email,
            'password' => Hash::make($tempPassword),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        // Assign teacher role
        $teacherRole = Role::firstOrCreate(['name' => 'teacher'], ['display_name' => 'Teacher', 'description' => 'Teacher with class-specific access']);
        $user->roles()->syncWithoutDetaching([$teacherRole->id]);

        // Link to teacher
        $teacher->update(['user_id' => $user->id]);

        return redirect()->back()->with('success', "Teacher account created. Temporary password: {$tempPassword}");
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
}
