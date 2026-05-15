<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('calendar_events') && !Schema::hasColumn('calendar_events', 'is_announcement')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                $table->boolean('is_announcement')->default(false)->after('is_all_day');
                $table->index('is_announcement');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('calendar_events') && Schema::hasColumn('calendar_events', 'is_announcement')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                $table->dropColumn('is_announcement');
            });
        }
    }
};
