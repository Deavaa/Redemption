<?php
/**
 * Fix Migration Blocker + Seed grade_scales
 * Run this FIRST before any other fix scripts
 */
echo "=== Fixing Migration Blocker ===\n\n";

$projectPath = __DIR__;
chdir($projectPath);

require $projectPath . '/vendor/autoload.php';

$app = require_once $projectPath . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Step 1: Fix the add_is_active_to_subjects_table migration
echo "Step 1: Checking is_active column in subjects table...\n";

$columnExists = Schema::hasColumn('subjects', 'is_active');
echo "  - is_active column exists: " . ($columnExists ? 'YES' : 'NO') . "\n";

// Find and fix the migration record
if ($columnExists) {
    $migrationFiles = glob($projectPath . '/database/migrations/*is_active*');
    foreach ($migrationFiles as $mfile) {
        $migrationName = basename($mfile, '.php');
        $recorded = DB::table('migrations')->where('migration', $migrationName)->exists();
        if (!$recorded) {
            $batch = DB::table('migrations')->max('batch') + 1;
            DB::table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => $batch
            ]);
            echo "  - Marked migration '$migrationName' as already run.\n";
        } else {
            echo "  - Migration '$migrationName' already recorded.\n";
        }
    }
}

// Step 2: Check for promotion tables
echo "\nStep 2: Checking promotion tables...\n";
$tables = ['grade_scales', 'promotion_settings', 'promotion_results'];
foreach ($tables as $tbl) {
    echo "  - $tbl: " . (Schema::hasTable($tbl) ? 'EXISTS' : 'MISSING') . "\n";
}

// Step 3: Run migrations
echo "\nStep 3: Running pending migrations...\n";
echo shell_exec('php artisan migrate --force 2>&1');

// Step 4: Verify
echo "\nStep 4: Verifying tables after migration...\n";
foreach ($tables as $tbl) {
    echo "  - $tbl: " . (Schema::hasTable($tbl) ? 'CREATED' : 'MISSING') . "\n";
}

// Step 5: Seed grade_scales
echo "\nStep 5: Seeding grade_scales...\n";
if (Schema::hasTable('grade_scales')) {
    $count = DB::table('grade_scales')->count();
    if ($count > 0) {
        echo "  - Already has $count records. Skipping.\n";
    } else {
        DB::table('grade_scales')->insert([
            ['grade' => 'A+', 'min_score' => 90, 'max_score' => 100, 'remark' => 'Excellent', 'grade_point' => 4.0, 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'A', 'min_score' => 80, 'max_score' => 89, 'remark' => 'Very Good', 'grade_point' => 3.5, 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'B+', 'min_score' => 75, 'max_score' => 79, 'remark' => 'Good', 'grade_point' => 3.0, 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'B', 'min_score' => 70, 'max_score' => 74, 'remark' => 'Fairly Good', 'grade_point' => 2.5, 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'C+', 'min_score' => 65, 'max_score' => 69, 'remark' => 'Above Average', 'grade_point' => 2.0, 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'C', 'min_score' => 60, 'max_score' => 64, 'remark' => 'Average', 'grade_point' => 1.5, 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'D', 'min_score' => 50, 'max_score' => 59, 'remark' => 'Below Average', 'grade_point' => 1.0, 'created_at' => now(), 'updated_at' => now()],
            ['grade' => 'F', 'min_score' => 0, 'max_score' => 49, 'remark' => 'Fail', 'grade_point' => 0.0, 'created_at' => now(), 'updated_at' => now()],
        ]);
        echo "  - Seeded 8 grade scale records!\n";
    }
} else {
    echo "  - ERROR: grade_scales table still missing!\n";
}

// Step 6: Seed promotion_settings defaults
echo "\nStep 6: Seeding promotion_settings defaults...\n";
if (Schema::hasTable('promotion_settings')) {
    $count = DB::table('promotion_settings')->count();
    if ($count > 0) {
        echo "  - Already has $count records. Skipping.\n";
    } else {
        DB::table('promotion_settings')->insert([
            'academic_year_id' => 1,
            'passing_percentage' => 50.00,
            'allow_conditional_promotion' => 1,
            'conditional_percentage' => 40.00,
            'max_failed_subjects_conditional' => 2,
            'auto_promotion_enabled' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "  - Seeded default promotion settings!\n";
    }
} else {
    echo "  - ERROR: promotion_settings table missing!\n";
}

// Step 7: Create models if missing
echo "\nStep 7: Creating models...\n";
$modelsDir = $projectPath . '/app/Models';

// GradeScale model
$gsPath = $modelsDir . '/GradeScale.php';
if (!file_exists($gsPath)) {
    $gsCONTENT = <<'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade',
        'min_score',
        'max_score',
        'remark',
        'grade_point',
    ];

    public static function getGradeForScore($score)
    {
        return static::where('min_score', '<=', $score)
            ->where('max_score', '>=', $score)
            ->first();
    }

    public static function getGradePointForScore($score)
    {
        $grade = static::getGradeForScore($score);
        return $grade ? $grade->grade_point : 0;
    }
}
EOF;
    file_put_contents($gsPath, $gsCONTENT);
    echo "  - Created GradeScale.php\n";
} else {
    echo "  - GradeScale.php already exists.\n";
}

// PromotionSetting model
$psPath = $modelsDir . '/PromotionSetting.php';
if (!file_exists($psPath)) {
    $psCONTENT = <<'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'passing_percentage',
        'allow_conditional_promotion',
        'conditional_percentage',
        'max_failed_subjects_conditional',
        'auto_promotion_enabled',
    ];

    protected $casts = [
        'allow_conditional_promotion' => 'boolean',
        'auto_promotion_enabled' => 'boolean',
        'passing_percentage' => 'decimal:2',
        'conditional_percentage' => 'decimal:2',
    ];
}
EOF;
    file_put_contents($psPath, $psCONTENT);
    echo "  - Created PromotionSetting.php\n";
} else {
    echo "  - PromotionSetting.php already exists.\n";
}

// PromotionResult model
$prPath = $modelsDir . '/PromotionResult.php';
if (!file_exists($prPath)) {
    $prCONTENT = <<'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'from_class_id',
        'to_class_id',
        'academic_year_id',
        'average_score',
        'total_score',
        'subjects_passed',
        'subjects_failed',
        'status',
        'remarks',
        'promoted_by',
    ];

    protected $casts = [
        'average_score' => 'decimal:2',
        'total_score' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function fromClass()
    {
        return $this->belongsTo(Classs::class, 'from_class_id');
    }

    public function toClass()
    {
        return $this->belongsTo(Classs::class, 'to_class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function promotedBy()
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }
}
EOF;
    file_put_contents($prPath, $prCONTENT);
    echo "  - Created PromotionResult.php\n";
} else {
    echo "  - PromotionResult.php already exists.\n";
}

echo "\n=== Migration Blocker Fix Complete ===\n";
echo "Next: Run fix_promotion_partB.php\n";
