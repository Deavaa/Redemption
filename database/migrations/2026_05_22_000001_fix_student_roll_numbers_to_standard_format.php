<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix student roll_numbers to use the standard G{grade}{section}-{NN} format.
 *
 * Before this fix, StudentController::generateRollNumber() returned plain
 * integers (e.g. 1, 2, 3) which caused:
 *   - Format mismatch with seeders (which use G1A-01, G5B-12, etc.)
 *   - Unique constraint violations (students in different sections could both get roll_number = 1)
 *
 * This migration:
 *   1. Converts integer-only roll numbers to the G{grade}{section}-{NN} format
 *   2. Re-assigns any conflicting roll numbers to avoid duplicates
 */
return new class extends Migration
{
    public function up(): void
    {
        // Get all students with non-standard roll numbers (plain integers, ROLL- format, etc.)
        // Standard format: G{number}{letter}-{number}  e.g. G1A-01, G10B-15
        $students = DB::table('students')
            ->orderBy('id')
            ->get();

        // Track used roll numbers to avoid conflicts
        $usedRollNumbers = [];
        $countFixed = 0;

        foreach ($students as $student) {
            $rollNumber = $student->roll_number;

            // Check if already in standard format (G{grade}{section}-{NN})
            if (preg_match('/^G\d+[A-Z]-\d{2}$/', $rollNumber)) {
                $usedRollNumbers[$rollNumber] = $student->id;
                continue;
            }

            // Need to convert this roll number to standard format
            $class = DB::table('classes')->where('id', $student->class_id)->first();
            $section = DB::table('sections')->where('id', $student->section_id)->first();

            if (!$class || !$section) {
                // Can't determine grade/section — assign a unique fallback
                $fallback = 'G0X-' . str_pad($student->id, 2, '0', STR_PAD_LEFT);
                DB::table('students')->where('id', $student->id)->update(['roll_number' => $fallback]);
                $usedRollNumbers[$fallback] = $student->id;
                $countFixed++;
                continue;
            }

            // Determine grade number
            $gradeNum = 0;
            if ($class->numeric_name) {
                $gradeNum = (int) $class->numeric_name;
            } elseif (preg_match('/(\d+)/', $class->name, $m)) {
                $gradeNum = (int) $m[1];
            }

            // Determine section letter
            $sectionLetter = 'A';
            if (preg_match('/([A-Z])$/i', $section->name, $m)) {
                $sectionLetter = strtoupper($m[1]);
            }

            // Find next available number for this prefix
            $prefix = 'G' . $gradeNum . $sectionLetter;
            $seq = 1;
            while (isset($usedRollNumbers[$prefix . '-' . str_pad($seq, 2, '0', STR_PAD_LEFT)])) {
                $seq++;
            }

            $newRollNumber = $prefix . '-' . str_pad($seq, 2, '0', STR_PAD_LEFT);

            DB::table('students')->where('id', $student->id)->update(['roll_number' => $newRollNumber]);
            $usedRollNumbers[$newRollNumber] = $student->id;
            $countFixed++;
        }

        if ($countFixed > 0) {
            echo "Fixed {$countFixed} student roll numbers to standard G{grade}{section}-{NN} format.\n";
        }
    }

    public function down(): void
    {
        // No down migration — we don't want to restore the old broken format
    }
};
