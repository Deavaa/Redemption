<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Sliders: title and image_path should be nullable (image may not be uploaded yet, title optional)
        DB::statement("ALTER TABLE sliders MODIFY COLUMN title VARCHAR(255) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE sliders MODIFY COLUMN image_path VARCHAR(255) NULL DEFAULT NULL");

        // Gallery Images: title and image_path should be nullable
        DB::statement("ALTER TABLE gallery_images MODIFY COLUMN title VARCHAR(255) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE gallery_images MODIFY COLUMN image_path VARCHAR(255) NULL DEFAULT NULL");

        // Gallery Videos: title should be nullable
        DB::statement("ALTER TABLE gallery_videos MODIFY COLUMN title VARCHAR(255) NULL DEFAULT NULL");

        // Team Members: name and designation should be nullable
        DB::statement("ALTER TABLE team_members MODIFY COLUMN name VARCHAR(255) NULL DEFAULT NULL");
        DB::statement("ALTER TABLE team_members MODIFY COLUMN designation VARCHAR(255) NULL DEFAULT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sliders MODIFY COLUMN title VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE sliders MODIFY COLUMN image_path VARCHAR(255) NOT NULL");

        DB::statement("ALTER TABLE gallery_images MODIFY COLUMN title VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE gallery_images MODIFY COLUMN image_path VARCHAR(255) NOT NULL");

        DB::statement("ALTER TABLE gallery_videos MODIFY COLUMN title VARCHAR(255) NOT NULL");

        DB::statement("ALTER TABLE team_members MODIFY COLUMN name VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE team_members MODIFY COLUMN designation VARCHAR(255) NOT NULL");
    }
};
