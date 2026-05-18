<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Clean up any student records that have "Auto-generated" or similar
        // placeholder text in their admission_number or roll_number fields
        DB::table('students')
            ->whereRaw("LOWER(admission_number) LIKE '%auto%'")
            ->update(['admission_number' => null]);

        DB::table('students')
            ->whereRaw("LOWER(roll_number) LIKE '%auto%'")
            ->update(['roll_number' => null]);

        // Now re-generate proper admission numbers for any that were nulled
        $students = DB::table('students')->whereNull('admission_number')->orderBy('id')->get();
        $year = date('Y');
        $counter = 1;

        // Get the max existing admission number to avoid conflicts
        $maxNum = DB::table('students')
            ->where('admission_number', 'like', $year . '-%')
            ->selectRaw("CAST(SUBSTRING(admission_number, -4) AS UNSIGNED) as num")
            ->orderByRaw('num DESC')
            ->value('num');

        if ($maxNum) {
            $counter = (int)$maxNum + 1;
        }

        foreach ($students as $student) {
            DB::table('students')
                ->where('id', $student->id)
                ->update(['admission_number' => $year . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT)]);
            $counter++;
        }

        // Fix any ID card records with "Auto-generated" in card_number
        DB::table('id_cards')
            ->whereRaw("LOWER(card_number) LIKE '%auto%'")
            ->get()
            ->each(function ($card) {
                DB::table('id_cards')
                    ->where('id', $card->id)
                    ->update(['card_number' => 'ID-' . str_pad($card->student_id, 5, '0', STR_PAD_LEFT)]);
            });
    }

    public function down(): void
    {
        // No down migration — we don't want to restore bad data
    }
};
