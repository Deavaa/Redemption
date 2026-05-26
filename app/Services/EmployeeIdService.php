<?php

namespace App\Services;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

/**
 * Employee ID Generation Service
 *
 * Generates unique employee IDs for staff members.
 * Format: EMP-{BRANCH_CODE}-{NNNN}
 * Example: EMP-LBU-0001, EMP-TUL-0001
 *
 * Branch codes:
 * - LBU = Lebu
 * - TUL = Tuludimtu
 * - HQ = Headquarters (for general managers, admin staff)
 */
class EmployeeIdService
{
    /**
     * Generate a unique employee ID for a user.
     *
     * @param int|null $branchId The user's primary branch
     * @return string The generated employee ID
     */
    public function generate(?int $branchId = null): string
    {
        $branchCode = $this->getBranchCode($branchId);

        // Get the next sequence number for this branch code
        $lastEmployee = User::where('employee_id', 'LIKE', "EMP-{$branchCode}-%")
            ->orderByRaw('CAST(SUBSTRING(employee_id, -4) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($lastEmployee && $lastEmployee->employee_id) {
            $parts = explode('-', $lastEmployee->employee_id);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }

        return "EMP-{$branchCode}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate and assign employee ID to a user.
     *
     * @param User $user
     * @param int|null $branchId
     * @return string The generated employee ID
     */
    public function assignToUser(User $user, ?int $branchId = null): string
    {
        if (!empty($user->employee_id)) {
            return $user->employee_id;
        }

        $employeeId = $this->generate($branchId ?? $user->branch_id);

        // Use direct DB update to avoid model events issues
        DB::table('users')->where('id', $user->id)->update(['employee_id' => $employeeId]);

        return $employeeId;
    }

    /**
     * Generate a default password for new users.
     *
     * @return string
     */
    public function getDefaultPassword(): string
    {
        return '123456';
    }

    /**
     * Get branch code from branch ID.
     *
     * @param int|null $branchId
     * @return string
     */
    private function getBranchCode(?int $branchId): string
    {
        if (!$branchId) {
            return 'HQ';
        }

        $branch = Branch::find($branchId);
        if (!$branch) {
            return 'HQ';
        }

        // Map known branch names to codes
        $name = strtolower(trim($branch->name));
        $codes = [
            'lebu' => 'LBU',
            'tuludimtu' => 'TUL',
        ];

        // Check if the branch name contains any of the known names
        foreach ($codes as $key => $code) {
            if (str_contains($name, $key)) {
                return $code;
            }
        }

        // Fallback: generate code from branch name (first 3 uppercase letters)
        $code = strtoupper(preg_replace('/[^a-zA-Z]/', '', $name));
        return substr($code, 0, 3) ?: 'BRH';
    }
}
