<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Branch;
use Illuminate\Support\Facades\DB;

/**
 * Teacher ID Generation Service
 *
 * Generates unique teacher IDs for teachers.
 * Format: TCH-{BRANCH_CODE}-{NNNN}
 * Example: TCH-LBU-0001, TCH-TUL-0001
 *
 * Branch codes:
 * - LBU = Lebu
 * - TUL = Tuludimtu
 * - HQ = Headquarters (fallback)
 */
class TeacherIdService
{
    /**
     * Generate a unique teacher ID.
     *
     * @param int|null $branchId The teacher's primary branch
     * @return string The generated teacher ID
     */
    public function generate(?int $branchId = null): string
    {
        $branchCode = $this->getBranchCode($branchId);

        // Get the next sequence number for this branch code
        $lastTeacher = Teacher::where('teacher_id_number', 'LIKE', "TCH-{$branchCode}-%")
            ->orderByRaw('CAST(SUBSTRING(teacher_id_number, -4) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($lastTeacher && $lastTeacher->teacher_id_number) {
            $parts = explode('-', $lastTeacher->teacher_id_number);
            $lastNumber = (int) end($parts);
            $nextNumber = $lastNumber + 1;
        }

        return "TCH-{$branchCode}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate and assign teacher ID to a teacher.
     *
     * @param Teacher $teacher
     * @param int|null $branchId
     * @return string The generated teacher ID
     */
    public function assignToTeacher(Teacher $teacher, ?int $branchId = null): string
    {
        if (!empty($teacher->teacher_id_number)) {
            return $teacher->teacher_id_number;
        }

        $teacherId = $this->generate($branchId ?? $teacher->branch_id);

        // Use direct DB update to avoid model events issues
        DB::table('teachers')->where('id', $teacher->id)->update(['teacher_id_number' => $teacherId]);

        return $teacherId;
    }

    /**
     * Get branch code from branch ID.
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

        $name = strtolower(trim($branch->name));
        $codes = [
            'lebu' => 'LBU',
            'tuludimtu' => 'TUL',
        ];

        foreach ($codes as $key => $code) {
            if (str_contains($name, $key)) {
                return $code;
            }
        }

        // Fallback: generate code from branch name
        $code = strtoupper(preg_replace('/[^a-zA-Z]/', '', $name));
        return substr($code, 0, 3) ?: 'BRH';
    }
}
