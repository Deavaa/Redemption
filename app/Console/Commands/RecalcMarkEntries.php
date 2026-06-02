<?php

namespace App\Console\Commands;

use App\Models\MarkEntry;
use App\Models\MarkEntryConfig;
use Illuminate\Console\Command;

class RecalcMarkEntries extends Command
{
    protected $signature = 'marks:recalc {--dry-run : Show what would change without saving}';
    protected $description = 'Recalculate ca_total, exam_total, grand_total and grade for all mark entries from raw fields using DB-driven config';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $entries = MarkEntry::all();
        $total = $entries->count();

        if ($total === 0) {
            $this->info('No mark entries found.');
            return 0;
        }

        // ── Load config from database (same as the model uses) ──
        $markFields = MarkEntry::getMarkFields();
        $caWeight = MarkEntryConfig::getCaWeight();
        $examWeight = MarkEntryConfig::getExamWeight();
        $precision = MarkEntryConfig::getRoundingPrecision();
        $gradeScale = MarkEntryConfig::getGradeScale();

        // Classify fields by category
        $caFields = [];
        $examFields = [];
        $caRawTotal = 0;
        foreach ($markFields as $field) {
            $cat = $field['category'] ?? 'ca';
            if ($cat === 'ca' || $cat === 'extra_ca') {
                $caFields[] = $field['col'];
                $caRawTotal += $field['max'];
            } elseif ($cat === 'exam') {
                $examFields[] = $field['col'];
            }
        }

        $this->info("Config: CA weight={$caWeight}, Exam weight={$examWeight}, CA raw total={$caRawTotal}, Precision={$precision}");
        $this->info("CA fields: " . implode(', ', $caFields));
        $this->info("Exam fields: " . implode(', ', $examFields));
        $this->info("Processing {$total} mark entries...");
        $bar = $this->output->createProgressBar($total);
        $fixed = 0;

        foreach ($entries as $entry) {
            // Sum raw CA marks
            $caRaw = 0;
            foreach ($caFields as $f) { $caRaw += floatval($entry->$f ?? 0); }

            // Sum raw exam marks
            $examRaw = 0;
            foreach ($examFields as $f) { $examRaw += floatval($entry->$f ?? 0); }

            // Scale CA: (caRaw / caRawTotal) * caWeight
            $caTotal = $caRawTotal > 0
                ? round(($caRaw / $caRawTotal) * $caWeight, $precision)
                : 0;

            // Cap exam at exam weight
            $examTotal = min($examRaw, $examWeight);

            // Grand total
            $grandTotal = round($caTotal + $examTotal, $precision);

            // Calculate grade from DB-driven grade scale
            $grade = MarkEntry::calcGrade($grandTotal, $gradeScale);

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
