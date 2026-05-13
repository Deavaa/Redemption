<?php

echo "=== School of Redemption Migration Generator ===\n\n";

require __DIR__ . '/vendor/autoload.php';
 $app = require_once __DIR__ . '/bootstrap/app.php';
 $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
 $kernel->bootstrap();

 $dir = database_path('migrations');
if (!is_dir($dir)) { mkdir($dir, 0755, true); }

 $tpl = <<<'TPL'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('__TBL__', function (Blueprint $t) {
__COLS__
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('__TBL__');
    }
};
TPL;

function mig($file, $table, $cols) {
    global $tpl, $dir;
    $c = str_replace(['__TBL__', '__COLS__'], [$table, $cols], $tpl);
    file_put_contents("$dir/$file", $c);
    echo "  [OK] $file\n";
}

mig('2024_01_01_000000_create_users_table.php', 'users', <<<'C'
            $t->id();
            $t->string('name');
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password');
            $t->enum('role', ['admin','teacher','staff','student','parent'])->default('staff');
            $t->string('phone')->nullable();
            $t->text('address')->nullable();
            $t->string('profile_photo')->nullable();
            $t->boolean('is_active')->default(true);
            $t->rememberToken();
            $t->timestamps();
            $t->softDeletes();
C);

mig('2024_01_01_000001_create_branches_table.php', 'branches', <<<'C'
            $t->id();
            $t->string('name');
            $t->text('address');
            $t->string('phone');
            $t->string('email');
            $t->decimal('gps_lat', 10, 8)->nullable();
            $t->decimal('gps_lng', 11, 8)->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
C);

mig('2024_01_01_000002_create_academic_years_table.php', 'academic_years', <<<'C'
            $t->id();
            $t->string('name');
            $t->date('start_date');
            $t->date('end_date');
            $t->boolean('is_current')->default(false);
            $t->timestamps();
C);

mig('2024_01_01_000003_create_terms_table.php', 'terms', <<<'C'
            $t->id();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->date('start_date');
            $t->date('end_date');
            $t->boolean('is_active')->default(true);
            $t->timestamps();
C);

mig('2024_01_01_000004_create_classes_table.php', 'classes', <<<'C'
            $t->id();
            $t->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('numeric_name')->nullable();
            $t->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
C);

mig('2024_01_01_000005_create_sections_table.php', 'sections', <<<'C'
            $t->id();
            $t->foreignId('class_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->integer('capacity')->default(40);
            $t->timestamps();
C);

mig('2024_01_01_000006_create_subjects_table.php', 'subjects', <<<'C'
            $t->id();
            $t->string('name');
            $t->string('code')->unique();
            $t->enum('type', ['theory','practical','both'])->default('theory');
            $t->text('description')->nullable();
            $t->timestamps();
C);

mig('2024_01_01_000007_create_students_table.php', 'students', <<<'C'
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $t->foreignId('class_id')->constrained()->cascadeOnDelete();
            $t->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->string('roll_number');
            $t->string('admission_number')->unique();
            $t->date('admission_date');
            $t->date('date_of_birth');
            $t->enum('gender', ['male','female','other']);
            $t->string('blood_group')->nullable();
            $t->string('religion')->nullable();
            $t->string('nationality')->default('Ethiopian');
            $t->string('previous_school')->nullable();
            $t->string('status')->default('active');
            $t->timestamps();
C);

mig('2024_01_01_000008_create_parents_table.php', 'parents', <<<'C'
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->string('father_name');
            $t->string('mother_name');
            $t->string('father_occupation')->nullable();
            $t->string('mother_occupation')->nullable();
            $t->string('father_phone')->nullable();
            $t->string('mother_phone')->nullable();
            $t->string('guardian_name')->nullable();
            $t->string('guardian_relation')->nullable();
            $t->string('guardian_phone')->nullable();
            $t->timestamps();
C);

mig('2024_01_01_000009_create_student_parent_table.php', 'student_parent', <<<'C'
            $t->id();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->foreignId('parent_id')->constrained()->cascadeOnDelete();
            $t->enum('relation', ['father','mother','guardian'])->default('father');
            $t->timestamps();
            $t->unique(['student_id', 'parent_id']);
C);

mig('2024_01_01_000010_create_teacher_assignments_table.php', 'teacher_assignments', <<<'C'
            $t->id();
            $t->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('class_id')->constrained()->cascadeOnDelete();
            $t->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['teacher_id','class_id','section_id','subject_id','academic_year_id']);
C);

mig('2024_01_01_000011_create_exams_table.php', 'exams', <<<'C'
            $t->id();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->foreignId('term_id')->constrained()->cascadeOnDelete();
            $t->foreignId('class_id')->constrained()->cascadeOnDelete();
            $t->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->enum('type', ['exam','quiz','assignment','project','midterm','final'])->default('exam');
            $t->decimal('total_marks', 8, 2)->default(100);
            $t->decimal('passing_marks', 8, 2)->default(50);
            $t->date('exam_date');
            $t->timestamps();
C);

mig('2024_01_01_000012_create_mark_entries_table.php', 'mark_entries', <<<'C'
            $t->id();
            $t->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->decimal('marks_obtained', 8, 2);
            $t->string('grade')->nullable();
            $t->text('remarks')->nullable();
            $t->timestamps();
            $t->unique(['exam_id', 'student_id']);
C);

mig('2024_01_01_000013_create_certificates_table.php', 'certificates', <<<'C'
            $t->id();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->enum('type', ['character','transfer','experience','achievement','completion'])->default('character');
            $t->string('certificate_number')->unique();
            $t->date('issue_date');
            $t->text('content')->nullable();
            $t->string('template')->default('default');
            $t->timestamps();
C);

mig('2024_01_01_000014_create_id_cards_table.php', 'id_cards', <<<'C'
            $t->id();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->string('card_number')->unique();
            $t->date('issue_date');
            $t->date('valid_until');
            $t->enum('status', ['active','expired','renewed','revoked'])->default('active');
            $t->timestamps();
C);

mig('2024_01_01_000015_create_progress_reports_table.php', 'progress_reports', <<<'C'
            $t->id();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->foreignId('term_id')->constrained()->cascadeOnDelete();
            $t->foreignId('class_id')->constrained()->cascadeOnDelete();
            $t->decimal('total_marks', 8, 2)->nullable();
            $t->decimal('percentage', 5, 2)->nullable();
            $t->string('grade')->nullable();
            $t->integer('rank')->nullable();
            $t->text('remarks')->nullable();
            $t->text('teacher_comment')->nullable();
            $t->timestamps();
C);

mig('2024_01_01_000016_create_performance_reports_table.php', 'performance_reports', <<<'C'
            $t->id();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->foreignId('term_id')->constrained()->cascadeOnDelete();
            $t->decimal('attendance_percentage', 5, 2)->default(0);
            $t->enum('behavior_rating', ['excellent','good','average','poor'])->default('good');
            $t->enum('sports_rating', ['excellent','good','average','poor'])->default('good');
            $t->enum('extracurricular_rating', ['excellent','good','average','poor'])->default('good');
            $t->enum('overall_rating', ['excellent','good','average','poor'])->default('good');
            $t->text('remarks')->nullable();
            $t->timestamps();
C);

mig('2024_01_01_000017_create_class_assets_table.php', 'class_assets', <<<'C'
            $t->id();
            $t->foreignId('class_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->integer('quantity')->default(1);
            $t->enum('condition', ['new','good','fair','poor','damaged'])->default('good');
            $t->date('purchase_date')->nullable();
            $t->decimal('purchase_price', 12, 2)->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
C);

mig('2024_01_01_000018_create_employee_assets_table.php', 'employee_assets', <<<'C'
            $t->id();
            $t->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $t->string('name');
            $t->integer('quantity')->default(1);
            $t->enum('condition', ['new','good','fair','poor','damaged'])->default('good');
            $t->date('issue_date');
            $t->date('return_date')->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
C);

mig('2024_01_01_000019_create_fees_table.php', 'fees', <<<'C'
            $t->id();
            $t->foreignId('class_id')->constrained()->cascadeOnDelete();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->string('fee_type');
            $t->decimal('amount', 12, 2);
            $t->date('due_date')->nullable();
            $t->text('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
C);

mig('2024_01_01_000020_create_fee_payments_table.php', 'fee_payments', <<<'C'
            $t->id();
            $t->foreignId('fee_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->decimal('amount_paid', 12, 2);
            $t->date('payment_date');
            $t->enum('payment_method', ['cash','bank','mobile','cheque','online'])->default('cash');
            $t->string('transaction_id')->nullable();
            $t->string('receipt_number')->unique();
            $t->enum('status', ['paid','partial','pending','overdue'])->default('pending');
            $t->timestamps();
C);

mig('2024_01_01_000021_create_leaves_table.php', 'leaves', <<<'C'
            $t->id();
            $t->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $t->enum('leave_type', ['sick','annual','maternity','paternity','unpaid','casual','emergency'])->default('annual');
            $t->date('start_date');
            $t->date('end_date');
            $t->integer('total_days');
            $t->text('reason')->nullable();
            $t->enum('status', ['pending','approved','rejected','cancelled'])->default('pending');
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
C);

mig('2024_01_01_000022_create_payrolls_table.php', 'payrolls', <<<'C'
            $t->id();
            $t->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $t->decimal('basic_salary', 12, 2);
            $t->decimal('allowances', 12, 2)->default(0);
            $t->decimal('deductions', 12, 2)->default(0);
            $t->decimal('tax', 12, 2)->default(0);
            $t->decimal('net_salary', 12, 2);
            $t->string('pay_period');
            $t->date('payment_date')->nullable();
            $t->enum('status', ['paid','pending','processed'])->default('pending');
            $t->timestamps();
C);

mig('2024_01_01_000023_create_budgets_table.php', 'budgets', <<<'C'
            $t->id();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->string('category');
            $t->decimal('allocated_amount', 14, 2);
            $t->decimal('spent_amount', 14, 2)->default(0);
            $t->text('description')->nullable();
            $t->enum('status', ['draft','approved','active','closed'])->default('draft');
            $t->timestamps();
C);

mig('2024_01_01_000024_create_income_expenses_table.php', 'income_expenses', <<<'C'
            $t->id();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->enum('type', ['income','expense']);
            $t->string('category');
            $t->decimal('amount', 14, 2);
            $t->date('date');
            $t->text('description')->nullable();
            $t->string('reference')->nullable();
            $t->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
C);

mig('2024_01_01_000025_create_finance_statements_table.php', 'finance_statements', <<<'C'
            $t->id();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $t->enum('statement_type', ['income_statement','balance_sheet','cash_flow','trial_balance'])->default('income_statement');
            $t->date('period_from');
            $t->date('period_to');
            $t->decimal('total_income', 14, 2)->default(0);
            $t->decimal('total_expense', 14, 2)->default(0);
            $t->decimal('net_balance', 14, 2)->default(0);
            $t->text('description')->nullable();
            $t->timestamps();
C);

mig('2024_01_01_000026_create_audits_table.php', 'audits', <<<'C'
            $t->id();
            $t->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $t->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $t->string('auditor_name');
            $t->date('audit_date');
            $t->text('findings')->nullable();
            $t->text('recommendations')->nullable();
            $t->enum('status', ['open','in_progress','closed','resolved'])->default('open');
            $t->timestamps();
C);

mig('2024_01_01_000027_create_team_members_table.php', 'team_members', <<<'C'
            $t->id();
            $t->string('name');
            $t->string('designation');
            $t->string('department')->nullable();
            $t->string('qualification')->nullable();
            $t->string('experience')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->string('photo')->nullable();
            $t->text('bio')->nullable();
            $t->integer('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
C);

mig('2024_01_01_000028_create_gallery_images_table.php', 'gallery_images', <<<'C'
            $t->id();
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('image_path');
            $t->string('category')->nullable();
            $t->integer('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
C);

mig('2024_01_01_000029_create_gallery_videos_table.php', 'gallery_videos', <<<'C'
            $t->id();
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('video_url');
            $t->string('thumbnail')->nullable();
            $t->integer('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
C);

mig('2024_01_01_000030_create_sliders_table.php', 'sliders', <<<'C'
            $t->id();
            $t->string('title');
            $t->string('subtitle')->nullable();
            $t->string('image_path');
            $t->string('link')->nullable();
            $t->integer('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
C);

mig('2024_01_01_000031_create_settings_table.php', 'settings', <<<'C'
            $t->id();
            $t->string('key')->unique();
            $t->text('value')->nullable();
            $t->string('group')->default('general');
            $t->enum('type', ['text','number','boolean','file','json'])->default('text');
            $t->text('description')->nullable();
            $t->timestamps();
C);

mig('2024_01_01_000032_create_contact_messages_table.php', 'contact_messages', <<<'C'
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->string('phone')->nullable();
            $t->string('subject');
            $t->text('message');
            $t->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $t->boolean('is_read')->default(false);
            $t->timestamps();
C);

echo "\n=== DONE! 33 migration files created ===\n";
echo "Next: php artisan migrate\n";
