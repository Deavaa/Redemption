<?php
namespace App\Http\Controllers\UserAccess;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ParentModel;
use App\Models\Student;
use App\Models\Role;
use App\Services\EmployeeIdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
        $employeeIdService = new EmployeeIdService();
        $defaultPassword = $employeeIdService->getDefaultPassword();

        if ($parent->user_id) {
            return redirect()->back()->with('error', 'This parent already has a user account.');
        }

        // Use phone as email since parents login with phone
        $phone = $parent->father_phone ?? $parent->guardian_phone ?? '';
        if ($phone) {
            $phone = $this->normalizePhone($phone);
        }
        $email = $phone ? $phone . '@parent.local' : 'parent' . $parent->id . '@parent.local';
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            $parent->update(['user_id' => $existingUser->id]);
            $parentRole = Role::firstOrCreate(['name' => 'parent'], ['display_name' => 'Parent', 'description' => 'Parent with view access to child records']);
            $existingUser->roles()->syncWithoutDetaching([$parentRole->id]);
            $existingUser->update(['role' => 'parent']);
            return redirect()->back()->with('success', 'Existing user linked to parent successfully.');
        }

        $user = User::create([
            'name' => $parent->guardian_name ?? $parent->father_name ?? 'Parent',
            'email' => $email,
            'password' => Hash::make($defaultPassword),
            'role' => 'parent',
            'phone' => $phone,
            'is_active' => true,
        ]);

        // Auto-generate employee ID
        $employeeId = $employeeIdService->assignToUser($user);

        $parentRole = Role::firstOrCreate(['name' => 'parent'], ['display_name' => 'Parent', 'description' => 'Parent with view access to child records']);
        $user->roles()->syncWithoutDetaching([$parentRole->id]);
        $parent->update(['user_id' => $user->id]);

        return redirect()->back()->with('success', "Parent account created. ID: {$employeeId}. Default password: {$defaultPassword}. Login with phone: {$phone}");
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $defaultPassword = (new EmployeeIdService())->getDefaultPassword();
        $user->update(['password' => Hash::make($defaultPassword)]);

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
