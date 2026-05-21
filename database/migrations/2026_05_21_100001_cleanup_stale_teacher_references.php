<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Clean up stale teacher_id references in classes and sections tables.
 *
 * This happens when a teacher is deleted but the foreign key's ON DELETE SET NULL
 * didn't fire (e.g., teacher was deleted via raw SQL, or the FK was added after
 * the teacher was already gone).
 *
 * This causes: "Integrity constraint violation: 1452 Cannot add or update a child row"
 * when trying to update a class that has a stale teacher_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Get all valid teacher IDs
        $validTeacherIds = DB::table('teachers')->pluck('id')->toArray();

        // Clean up classes table — set teacher_id to NULL where it references a deleted teacher
        $staleClasses = DB::table('classes')
            ->whereNotNull('teacher_id')
            ->whereNotIn('teacher_id', $validTeacherIds)
            ->update(['teacher_id' => null]);

        // Clean up sections table — set teacher_id to NULL where it references a deleted teacher
        $staleSections = DB::table('sections')
            ->whereNotNull('teacher_id')
            ->whereNotIn('teacher_id', $validTeacherIds)
            ->update(['teacher_id' => null]);

        // Log what was cleaned up (visible in artisan migrate output)
        if ($staleClasses > 0 || $staleSections > 0) {
            echo "  Cleaned up stale teacher references: {$staleClasses} class(es), {$staleSections} section(s)\n";
        }
    }

    public function down(): void
    {
        // No down migration — we can't restore the deleted teacher IDs
    }
};
