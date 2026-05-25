<?php

namespace App\Console\Commands;

use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Console\Command;

class BackfillEnrollments extends Command
{
    protected $signature = 'enrollments:backfill {--dry-run : Show what would be created without creating}';
    protected $description = 'Backfill StudentEnrollment records for existing students who do not have them';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        // Get current or latest academic year
        $currentAy = AcademicYear::where('is_current', true)->first()
            ?? AcademicYear::orderBy('id', 'desc')->first();

        if (!$currentAy) {
            $this->error('No academic years found. Please create an academic year first.');
            return 1;
        }

        $this->info("Using Academic Year: {$currentAy->name} (ID: {$currentAy->id})");

        // Find students without enrollment records
        $studentsWithoutEnrollments = Student::whereDoesntHave('enrollments')->get();
        $this->info("Found {$studentsWithoutEnrollments->count()} students without enrollment records.");

        if ($studentsWithoutEnrollments->isEmpty()) {
            $this->info('All students already have enrollment records. Nothing to do.');
            return 0;
        }

        // Also find students who have enrollments but not for the current academic year
        $studentsWithoutCurrentEnrollment = Student::whereHas('enrollments', function ($q) use ($currentAy) {
            $q->where('academic_year_id', '!=', $currentAy->id);
        })->orWhereDoesntHave('enrollments')
          ->whereNotExists(function ($q) use ($currentAy) {
              $q->selectRaw(1)
                  ->from('student_enrollments')
                  ->whereColumn('student_enrollments.student_id', 'students.id')
                  ->where('academic_year_id', $currentAy->id);
          })
          ->get();

        $this->info("Found {$studentsWithoutCurrentEnrollment->count()} students without enrollment in the current academic year.");

        $created = 0;
        $skipped = 0;

        foreach ($studentsWithoutCurrentEnrollment as $student) {
            // Skip if already enrolled in current AY
            $exists = StudentEnrollment::where('student_id', $student->id)
                ->where('academic_year_id', $currentAy->id)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $this->line("  [DRY-RUN] Would create enrollment for: {$student->full_name} (ID: {$student->id})");
                $created++;
                continue;
            }

            StudentEnrollment::create([
                'student_id' => $student->id,
                'academic_year_id' => $currentAy->id,
                'branch_id' => $student->branch_id,
                'class_id' => $student->class_id,
                'section_id' => $student->section_id,
                'roll_number' => $student->roll_number,
                'enrollment_date' => $student->admission_date ?? now()->toDateString(),
                'status' => $student->status === 'active' ? 'enrolled' : $student->status,
                'enrollment_type' => 'new',
                'registration_fee' => 0,
                'registration_fee_paid' => 0,
                'registration_fee_status' => 'waived',
                'enrolled_by' => 1,
            ]);

            $created++;
        }

        if ($dryRun) {
            $this->info("[DRY-RUN] Would create {$created} enrollment records. Skipped {$skipped}.");
        } else {
            $this->info("Created {$created} enrollment records. Skipped {$skipped}.");
        }

        return 0;
    }
}
