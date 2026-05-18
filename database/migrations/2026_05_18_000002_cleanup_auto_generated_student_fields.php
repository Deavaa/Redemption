<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix admission_number records that contain "auto-generated" or similar placeholder text
        // Since admission_number is NOT NULL, we must replace directly with proper values
        $year = date('Y');

        // Get the max existing admission number to avoid conflicts
        $maxNum = DB::table('students')
            ->where('admission_number', 'like', $year . '-%')
            ->selectRaw("CAST(SUBSTRING(admission_number, -4) AS UNSIGNED) as num")
            ->orderByRaw('num DESC')
            ->value('num');

        $counter = $maxNum ? ((int)$maxNum + 1) : 1;

        // Directly update each student with auto-generated admission_number
        $autoAdmissionStudents = DB::table('students')
            ->whereRaw("LOWER(admission_number) LIKE '%auto%'")
            ->orderBy('id')
            ->get();

        foreach ($autoAdmissionStudents as $student) {
            $newAdmissionNumber = $year . '-' . str_pad($counter, 4, '0', STR_PAD_LEFT);
            DB::table('students')
                ->where('id', $student->id)
                ->update(['admission_number' => $newAdmissionNumber]);
            $counter++;
        }

        // Directly update each student with auto-generated roll_number
        $maxRoll = DB::table('students')
            ->where('roll_number', 'like', 'ROLL-%')
            ->selectRaw("CAST(SUBSTRING(roll_number, -5) AS UNSIGNED) as num")
            ->orderByRaw('num DESC')
            ->value('num');

        $rollCounter = $maxRoll ? ((int)$maxRoll + 1) : 1;

        $autoRollStudents = DB::table('students')
            ->whereRaw("LOWER(roll_number) LIKE '%auto%'")
            ->orderBy('id')
            ->get();

        foreach ($autoRollStudents as $student) {
            $newRollNumber = 'ROLL-' . str_pad($rollCounter, 5, '0', STR_PAD_LEFT);
            DB::table('students')
                ->where('id', $student->id)
                ->update(['roll_number' => $newRollNumber]);
            $rollCounter++;
        }

        // Fix any ID card records with "Auto-generated" in card_number
        $autoCards = DB::table('id_cards')
            ->whereRaw("LOWER(card_number) LIKE '%auto%'")
            ->get();

        foreach ($autoCards as $card) {
            $newCardNumber = 'ID-' . str_pad($card->student_id, 5, '0', STR_PAD_LEFT);
            // Make sure it doesn't conflict with existing card numbers
            $exists = DB::table('id_cards')
                ->where('card_number', $newCardNumber)
                ->where('id', '!=', $card->id)
                ->exists();

            if ($exists) {
                $newCardNumber = 'ID-' . str_pad($card->id, 5, '0', STR_PAD_LEFT);
            }

            DB::table('id_cards')
                ->where('id', $card->id)
                ->update(['card_number' => $newCardNumber]);
        }
    }

    public function down(): void
    {
        // No down migration — we don't want to restore bad data
    }
};
