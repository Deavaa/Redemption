<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Make class_id and subject_id nullable on exams table
        // so exams can apply to all classes / all subjects when not specified
        DB::statement("ALTER TABLE exams MODIFY COLUMN class_id BIGINT UNSIGNED NULL DEFAULT NULL");
        DB::statement("ALTER TABLE exams MODIFY COLUMN subject_id BIGINT UNSIGNED NULL DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE exams MODIFY COLUMN class_id BIGINT UNSIGNED NOT NULL");
        DB::statement("ALTER TABLE exams MODIFY COLUMN subject_id BIGINT UNSIGNED NOT NULL");
    }
};
