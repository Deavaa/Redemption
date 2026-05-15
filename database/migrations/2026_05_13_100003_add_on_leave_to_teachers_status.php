<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up() {
        // Change enum to include on_leave
        DB::statement("ALTER TABLE teachers MODIFY COLUMN status ENUM('active','inactive','on_leave') NOT NULL DEFAULT 'active'");
    }

    public function down() {
        DB::statement("ALTER TABLE teachers MODIFY COLUMN status ENUM('active','inactive') NOT NULL DEFAULT 'active'");
    }
};
