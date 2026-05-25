<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add employee_id to users table (auto-generated staff/employee ID)
        if (!Schema::hasColumn('users', 'employee_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('employee_id')->nullable()->unique()->after('id_number');
            });
        }

        // 2. Add report_period and report_grouping to report_documents table
        if (Schema::hasTable('report_documents')) {
            Schema::table('report_documents', function (Blueprint $table) {
                if (!Schema::hasColumn('report_documents', 'report_period')) {
                    $table->string('report_period')->nullable()->after('term_id'); // e.g. '2026-01' for monthly, '2026-Q1' for quarterly, '2026-H1' for half-year, '2026' for yearly
                }
                if (!Schema::hasColumn('report_documents', 'report_grouping')) {
                    $table->enum('report_grouping', ['monthly', 'quarterly', 'half_year', 'yearly'])->default('monthly')->after('report_period');
                }
            });
        }

        // 3. Add calendar event approval fields
        if (Schema::hasTable('calendar_events')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                if (!Schema::hasColumn('calendar_events', 'is_approved')) {
                    $table->boolean('is_approved')->default(false)->after('is_announcement');
                }
                if (!Schema::hasColumn('calendar_events', 'approved_by')) {
                    $table->unsignedBigInteger('approved_by')->nullable()->after('is_approved');
                }
                if (!Schema::hasColumn('calendar_events', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approved_by');
                }
                if (!Schema::hasColumn('calendar_events', 'scope')) {
                    $table->enum('scope', ['school', 'branch'])->default('school')->after('branch_id');
                }
            });
        }

        // 4. Create employee_branch pivot table (for multi-branch teaching assignments)
        if (!Schema::hasTable('employee_branch')) {
            Schema::create('employee_branch', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('branch_id');
                $table->enum('role_in_branch', ['primary', 'secondary', 'visiting'])->default('secondary');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
                $table->unique(['user_id', 'branch_id']);
            });
        }

        // 5. Add branch_id to teachers table for primary branch assignment
        if (Schema::hasTable('teachers')) {
            if (!Schema::hasColumn('teachers', 'branch_id')) {
                Schema::table('teachers', function (Blueprint $table) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('department_id');
                    $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
                });
            }
        }

        // 6. Make branches.principal_id nullable (a branch may have multiple principals)
        // This is already likely nullable but let's ensure it
        if (Schema::hasColumn('branches', 'principal_id')) {
            // Already exists, no change needed
        }

        // 7. Create branch_principals pivot table (branch can have multiple principals)
        if (!Schema::hasTable('branch_principals')) {
            Schema::create('branch_principals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id');
                $table->unsignedBigInteger('teacher_id'); // or user_id
                $table->boolean('is_primary')->default(false); // primary vs secondary principal
                $table->date('assigned_date')->nullable();
                $table->timestamps();

                $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
                $table->foreign('teacher_id')->references('id')->on('teachers')->onDelete('cascade');
                $table->unique(['branch_id', 'teacher_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('branch_principals')) {
            Schema::dropIfExists('branch_principals');
        }
        if (Schema::hasTable('employee_branch')) {
            Schema::dropIfExists('employee_branch');
        }

        if (Schema::hasTable('calendar_events')) {
            Schema::table('calendar_events', function (Blueprint $table) {
                $cols = ['is_approved', 'approved_by', 'approved_at', 'scope'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('calendar_events', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('report_documents')) {
            Schema::table('report_documents', function (Blueprint $table) {
                $cols = ['report_period', 'report_grouping'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('report_documents', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasColumn('users', 'employee_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('employee_id');
            });
        }

        if (Schema::hasTable('teachers') && Schema::hasColumn('teachers', 'branch_id')) {
            Schema::table('teachers', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }
    }
};
