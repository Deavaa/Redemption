<?php

namespace App\Console\Commands;

use App\Models\Teacher;
use App\Models\User;
use App\Models\Role;
use App\Services\EmployeeIdService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Backfill User accounts for existing Teachers that don't have one.
 *
 * When teachers were created before the auto-user-linking feature,
 * they won't have a corresponding User record. This command creates
 * those missing user accounts so teachers appear in the Staff list.
 */
class BackfillTeacherUsers extends Command
{
    protected $signature = 'backfill:teacher-users';
    protected $description = 'Create User accounts for existing Teachers that lack a linked user_id';

    public function handle(): int
    {
        $teachers = Teacher::whereNull('user_id')->get();
        $count = $teachers->count();

        if ($count === 0) {
            $this->info('All teachers already have user accounts. Nothing to do.');
            return self::SUCCESS;
        }

        $this->info("Found {$count} teachers without user accounts. Creating them now...");

        $employeeIdService = new EmployeeIdService();
        $defaultPassword = $employeeIdService->getDefaultPassword();
        $created = 0;

        foreach ($teachers as $teacher) {
            try {
                // Generate email
                $teacherEmail = $teacher->email;
                if (empty($teacherEmail) || str_contains($teacherEmail, '@school.local') === false) {
                    // If the teacher has no email or it's a real email, use it
                    if (empty($teacherEmail)) {
                        $slug = Str::slug($teacher->full_name, '');
                        $teacherEmail = $slug . '_' . $teacher->id . '@school.local';
                    }
                }

                // Check if a user with this email already exists
                $existingUser = User::where('email', $teacherEmail)->first();
                if ($existingUser) {
                    // Link existing user to teacher
                    $teacher->update(['user_id' => $existingUser->id]);
                    $this->line("  ✓ Linked existing user #{$existingUser->id} to teacher #{$teacher->id} ({$teacher->full_name})");
                    $created++;
                    continue;
                }

                // Also check if there's a user with same name and role=teacher
                $existingByName = User::where('name', $teacher->full_name)
                    ->where('role', 'teacher')
                    ->first();
                if ($existingByName) {
                    $teacher->update(['user_id' => $existingByName->id]);
                    $this->line("  ✓ Linked existing user #{$existingByName->id} to teacher #{$teacher->id} ({$teacher->full_name}) by name match");
                    $created++;
                    continue;
                }

                // Generate employee ID
                $employeeId = $employeeIdService->generate($teacher->branch_id);

                // Create new user account
                $user = User::create([
                    'name'         => $teacher->full_name,
                    'email'        => $teacherEmail,
                    'password'     => Hash::make($defaultPassword),
                    'role'         => 'teacher',
                    'branch_id'    => $teacher->branch_id,
                    'phone'        => $teacher->phone,
                    'gender'       => $teacher->gender,
                    'qualification'=> $teacher->qualification,
                    'address'      => $teacher->address,
                    'employee_id'  => $employeeId,
                    'is_active'    => $teacher->status === 'active',
                ]);

                // Assign RBAC role
                try {
                    $rbacRole = Role::where('name', 'teacher')->first();
                    if ($rbacRole) {
                        $user->roles()->sync([$rbacRole->id]);
                    }
                } catch (\Throwable $e) {}

                // Link user to teacher
                $teacher->update(['user_id' => $user->id, 'email' => $teacherEmail]);

                $this->line("  ✓ Created user #{$user->id} for teacher #{$teacher->id} ({$teacher->full_name}) - Employee ID: {$employeeId}");
                $created++;
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed for teacher #{$teacher->id} ({$teacher->full_name}): {$e->getMessage()}");
            }
        }

        $this->info("Done! Created/linked {$created} user accounts for teachers.");
        $this->info("Default password for new accounts: {$defaultPassword}");

        return self::SUCCESS;
    }
}
