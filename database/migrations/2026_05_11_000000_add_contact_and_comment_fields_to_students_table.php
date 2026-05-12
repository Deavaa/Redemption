<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('last_name');
            $table->string('phone')->nullable()->after('email');
            $table->text('address')->nullable()->after('phone');
            $table->string('guardian_name')->nullable()->after('address');
            $table->string('guardian_phone')->nullable()->after('guardian_name');
            $table->text('notes')->nullable()->after('guardian_phone');
            $table->text('teacher_comments')->nullable()->after('notes');
            $table->text('admin_comments')->nullable()->after('teacher_comments');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'phone',
                'address',
                'guardian_name',
                'guardian_phone',
                'notes',
                'teacher_comments',
                'admin_comments',
            ]);
        });
    }
};
