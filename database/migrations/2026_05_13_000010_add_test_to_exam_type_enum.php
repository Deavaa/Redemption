<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE exams MODIFY COLUMN type ENUM('exam','quiz','test','midterm','final','assignment','project') NOT NULL DEFAULT 'exam'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE exams MODIFY COLUMN type ENUM('exam','quiz','assignment','project','midterm','final') NOT NULL DEFAULT 'exam'");
    }
};
