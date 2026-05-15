<?php
namespace App\Http\Controllers\UserAccess;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentAccessController extends Controller
{
    public function index()
    {
        $students = Student::with(['user', 'classroom', 'section'])->orderBy('first_name')->paginate(50);
        $studentRole = Role::where('name', 'student')->first();
        return view('admin.user-access.students', compact('students', 'studentRole'));
    }

    public function createAccount(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::findOrFail($request->student_id);

        if ($student->user_id) {
            return redirect()->back()->with('error', 'This student already has a user account.');
        }

        $existingUser = User::where('email', $student->email)->first();
        if ($existingUser) {
            $student->update(['user_id' => $existingUser->id]);
            $studentRole = Role::firstOrCreate(['name' => 'student'], ['display_name' => 'Student', 'description' => 'Student with view-only access']);
            $existingUser->roles()->syncWithoutDetaching([$studentRole->id]);
            $existingUser->update(['role' => 'student']);
            return redirect()->back()->with('success', 'Existing user linked to student successfully.');
        }

        $tempPassword = Str::random(10);
        $user = User::create([
            'name' => $student->first_name . ' ' . $student->last_name,
            'email' => $student->email ?? $student->admission_number . '@school.local',
            'password' => Hash::make($tempPassword),
            'role' => 'student',
            'is_active' => true,
        ]);

        $studentRole = Role::firstOrCreate(['name' => 'student'], ['display_name' => 'Student', 'description' => 'Student with view-only access']);
        $user->roles()->syncWithoutDetaching([$studentRole->id]);
        $student->update(['user_id' => $user->id]);

        return redirect()->back()->with('success', "Student account created. Temporary password: {$tempPassword}");
    }

    public function bulkCreate(Request $request)
    {
        $request->validate([
            'class_id' => 'nullable|exists:classes,id',
        ]);

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
                $tempPassword = Str::random(10);
                $user = User::create([
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'email' => $email,
                    'password' => Hash::make($tempPassword),
                    'role' => 'student',
                    'is_active' => true,
                ]);
                $studentRole = Role::firstOrCreate(['name' => 'student'], ['display_name' => 'Student', 'description' => 'Student with view-only access']);
                $user->roles()->syncWithoutDetaching([$studentRole->id]);
                $student->update(['user_id' => $user->id]);
            }
            $count++;
        }

        return redirect()->back()->with('success', "{$count} student accounts created/linked.");
    }
}
