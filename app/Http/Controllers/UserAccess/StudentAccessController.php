<?php
namespace App\Http\Controllers\UserAccess;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Role;
use App\Services\EmployeeIdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentAccessController extends Controller
{
    public function index()
    {
        $students = Student::with(['user', 'classroom', 'section'])->orderBy('full_name')->paginate(50);
        $studentRole = Role::where('name', 'student')->first();
        return view('admin.user-access.students', compact('students', 'studentRole'));
    }

    public function createAccount(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::findOrFail($request->student_id);
        $employeeIdService = new EmployeeIdService();
        $defaultPassword = $employeeIdService->getDefaultPassword();

        if ($student->user_id) {
            return redirect()->back()->with('error', 'This student already has a user account.');
        }

        $existingUser = User::where('email', $student->email)->first();
        if ($existingUser) {
            $student->update(['user_id' => $existingUser->id]);
            $studentRole = Role::firstOrCreate(['name' => 'student'], ['display_name' => 'Student', 'description' => 'Student with view-only access']);
            $existingUser->roles()->syncWithoutDetaching([$studentRole->id]);
            $existingUser->update(['role' => 'student']);
            $employeeIdService->assignToUser($existingUser, $student->branch_id);
            return redirect()->back()->with('success', 'Existing user linked to student successfully.');
        }

        // Normalize phone
        $phone = $student->phone;
        if ($phone) {
            $phone = $this->normalizePhone($phone);
        }

        $user = User::create([
            'name' => $student->full_name,
            'email' => $student->email ?? $student->admission_number . '@school.local',
            'password' => Hash::make($defaultPassword),
            'role' => 'student',
            'phone' => $phone,
            'branch_id' => $student->branch_id,
            'is_active' => true,
        ]);

        // Auto-generate employee/student ID
        $employeeId = $employeeIdService->assignToUser($user, $student->branch_id);

        $studentRole = Role::firstOrCreate(['name' => 'student'], ['display_name' => 'Student', 'description' => 'Student with view-only access']);
        $user->roles()->syncWithoutDetaching([$studentRole->id]);
        $student->update(['user_id' => $user->id]);

        return redirect()->back()->with('success', "Student account created. ID: {$employeeId}. Default password: {$defaultPassword}");
    }

    public function bulkCreate(Request $request)
    {
        $request->validate([
            'class_id' => 'nullable|exists:classes,id',
        ]);

        $employeeIdService = new EmployeeIdService();
        $defaultPassword = $employeeIdService->getDefaultPassword();
        $query = Student::whereNull('user_id');
        if ($request->class_id) {
            $query->where('class_id', $request->class_id);
        }
        $students = $query->get();
        $count = 0;

        foreach ($students as $student) {
            if ($student->user_id) continue;

            $email = $student->email ?? $student->admission_number . '@school.local';
            $existingUser = User::where('email', $email)->first();

            if ($existingUser) {
                $student->update(['user_id' => $existingUser->id]);
                $studentRole = Role::firstOrCreate(['name' => 'student'], ['display_name' => 'Student', 'description' => 'Student with view-only access']);
                $existingUser->roles()->syncWithoutDetaching([$studentRole->id]);
                $existingUser->update(['role' => 'student']);
            } else {
                $user = User::create([
                    'name' => $student->full_name,
                    'email' => $email,
                    'password' => Hash::make($defaultPassword),
                    'role' => 'student',
                    'branch_id' => $student->branch_id,
                    'is_active' => true,
                ]);
                $employeeIdService->assignToUser($user, $student->branch_id);
                $studentRole = Role::firstOrCreate(['name' => 'student'], ['display_name' => 'Student', 'description' => 'Student with view-only access']);
                $user->roles()->syncWithoutDetaching([$studentRole->id]);
                $student->update(['user_id' => $user->id]);
            }
            $count++;
        }

        return redirect()->back()->with('success', "{$count} student accounts created/linked. Default password: {$defaultPassword}");
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
