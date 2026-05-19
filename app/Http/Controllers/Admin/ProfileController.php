<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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
            'password' => 'required|string|min:4|confirmed',
        ]);

        $user = auth()->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
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
        $defaultPassword = '123456';
        $targetUser->update(['password' => Hash::make($defaultPassword)]);

        return redirect()->back()->with('success', "Password for {$targetUser->name} has been reset to default: {$defaultPassword}");
    }
}
