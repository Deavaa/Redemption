<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmployeeIdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show the admin profile / change-password form.
     */
    public function index()
    {
        $user = auth()->user();
        return view('admin.profile.index', compact('user'));
    }

    /**
     * Update the admin's password.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = auth()->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Update password — User model cast handles hashing.
        // Also clear must_change_password since the user has now set their own password.
        $user->update([
            'password' => $request->password,
            'must_change_password' => false,
        ]);

        return redirect()->route('admin.profile')->with('success', 'Password changed successfully.');
    }

    /**
     * Reset any user's password to the default (admin-only).
     * Used when admin needs to reset another user's password from the user-access pages.
     */
    public function resetUserPassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $targetUser = User::findOrFail($request->user_id);
        $defaultPassword = (new EmployeeIdService())->getDefaultPassword();
        // Rely on model cast for hashing. Set must_change_password=true so the
        // user is prompted to change it on next login.
        $targetUser->update([
            'password' => $defaultPassword,
            'must_change_password' => true,
        ]);

        // Avoid echoing the actual password back in the flash message.
        // Admins can see the default password convention in the user-access page.
        return redirect()->back()->with('success', "Password for {$targetUser->name} has been reset to the default password. Please communicate it to the user securely.");
    }

    /**
     * Generate employee IDs for all existing users who don't have one yet.
     * This is a one-time migration endpoint for admin use.
     */
    public function generateMissingEmployeeIds()
    {
        $service = new EmployeeIdService();
        $users = User::whereNull('employee_id')
            ->whereNotIn('role', ['student', 'parent'])
            ->get();

        $count = 0;
        foreach ($users as $user) {
            $service->assignToUser($user, $user->branch_id);
            $count++;
        }

        return redirect()->back()->with('success', "Generated employee IDs for {$count} staff members.");
    }
}
