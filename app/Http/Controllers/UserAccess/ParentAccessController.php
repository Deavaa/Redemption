<?php
namespace App\Http\Controllers\UserAccess;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ParentAccessController extends Controller
{
    public function index()
    {
        $parents = ParentModel::with(['user', 'students'])->orderBy('guardian_name')->paginate(50);
        $parentRole = Role::where('name', 'parent')->first();
        return view('admin.user-access.parents', compact('parents', 'parentRole'));
    }

    public function createAccount(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|exists:parents,id',
        ]);

        $parent = ParentModel::findOrFail($request->parent_id);

        if ($parent->user_id) {
            return redirect()->back()->with('error', 'This parent already has a user account.');
        }

        $email = $parent->guardian_phone ? $parent->guardian_phone . '@parent.local' : 'parent' . $parent->id . '@parent.local';
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            $parent->update(['user_id' => $existingUser->id]);
            $parentRole = Role::firstOrCreate(['name' => 'parent'], ['display_name' => 'Parent', 'description' => 'Parent with view access to child records']);
            $existingUser->roles()->syncWithoutDetaching([$parentRole->id]);
            $existingUser->update(['role' => 'parent']);
            return redirect()->back()->with('success', 'Existing user linked to parent successfully.');
        }

        $defaultPassword = '123456';
        $user = User::create([
            'name' => $parent->guardian_name ?? $parent->father_name ?? 'Parent',
            'email' => $email,
            'password' => Hash::make($defaultPassword),
            'role' => 'parent',
            'is_active' => true,
        ]);

        $parentRole = Role::firstOrCreate(['name' => 'parent'], ['display_name' => 'Parent', 'description' => 'Parent with view access to child records']);
        $user->roles()->syncWithoutDetaching([$parentRole->id]);
        $parent->update(['user_id' => $user->id]);

        return redirect()->back()->with('success', "Parent account created. Default password: {$defaultPassword}");
    }

    /**
     * Reset a parent user's password to the default password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $defaultPassword = '123456';
        $user->update(['password' => Hash::make($defaultPassword)]);

        return redirect()->back()->with('success', "Password reset to default: {$defaultPassword}");
    }
}
