<?php

namespace App\Console\Commands;

use App\Models\MarkEntry;
use Illuminate\Console\Command;

class RecalcMarkEntries extends Command
{
    protected $signature = 'marks:recalc {--dry-run : Show what would change without saving}';
    protected $description = 'Recalculate ca_total, exam_total, grand_total and grade for all mark entries from raw fields';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $entries = MarkEntry::all();
        $total = $entries->count();

        if ($total === 0) {
            $this->info('No mark entries found.');
            return 0;
        }

        $this->info("Processing {$total} mark entries...");
        $bar = $this->output->createProgressBar($total);
        $fixed = 0;

        foreach ($entries as $entry) {
            $caFields = ['ca1','ca2','ca3','ca4','ca5','ca6','ca7','ca8','ca9','ca10','conduct','handwriting','creativity'];
            $examFields = ['test1','test2','mid_term','final_exam'];

            $caRaw = 0;
            foreach ($caFields as $f) { $caRaw += floatval($entry->$f ?? 0); }
            $examRaw = 0;
            foreach ($examFields as $f) { $examRaw += floatval($entry->$f ?? 0); }

            $caTotal = round(($caRaw / 70) * 30, 2);
            $examTotal = min($examRaw, 70);
            $grandTotal = round($caTotal + $examTotal, 2);

            // Calculate grade
            if ($grandTotal <= 0) $grade = 'I';
            elseif ($grandTotal >= 80) $grade = 'A';
            elseif ($grandTotal >= 60) $grade = 'B';
            elseif ($grandTotal >= 50) $grade = 'C';
            elseif ($grandTotal >= 40) $grade = 'D';
            else $grade = 'F';

            $changed = false;
            if (floatval($entry->ca_total) !== $caTotal) $changed = true;
            if (floatval($entry->exam_total) !== $examTotal) $changed = true;
            if (floatval($entry->grand_total) !== $grandTotal) $changed = true;
            if ($entry->grade !== $grade) $changed = true;

            if ($changed) {
                $fixed++;
                if ($dryRun) {
                    $this->warn("\n  Entry #{$entry->id} (Student:{$entry->student_id} Subject:{$entry->subject_id})");
                    $this->line("    ca_total:   {$entry->ca_total} → {$caTotal}");
                    $this->line("    exam_total: {$entry->exam_total} → {$examTotal}");
                    $this->line("    grand_total:{$entry->grand_total} → {$grandTotal}");
                    $this->line("    grade:      {$entry->grade} → {$grade}");
                } else {
                    $entry->ca_total = $caTotal;
                    $entry->exam_total = $examTotal;
                    $entry->grand_total = $grandTotal;
                    $entry->grade = $grade;
                    $entry->marks_obtained = $grandTotal;
                    $entry->save();
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($dryRun) {
            $this->info("Dry run complete. {$fixed} of {$total} entries would be fixed.");
        } else {
            $this->info("Done! Fixed {$fixed} of {$total} entries.");
        }

        return 0;
    }
}
