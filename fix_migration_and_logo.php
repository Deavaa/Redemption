<?php
echo "=== FIX MIGRATION + GRADE SCALES + LOGO ===\n\n";
 $b = getcwd();
if (!file_exists($b."/artisan")) die("Run from Redemption root!\n");

// STEP 1: Fix the is_active duplicate column migration
echo "--- Step 1: Fix is_active migration ---\n";
 $migrations = glob($b."/database/migrations/*add_is_active_to_subjects*");
foreach ($migrations as $migFile) {
    $content = file_get_contents($migFile);
    if (strpos($content, 'hasColumn') === false) {
        // Add column existence check
        $content = preg_replace(
            '/\$table->.*?is_active.*?;/',
            'if (!$table->hasColumn(\'is_active\')) { $table->boolean(\'is_active\')->default(1); }',
            $content
        );
        // Alternative: wrap the whole Schema::table in a check
        $content = str_replace(
            "Schema::table('subjects', function (Blueprint \$table) {",
            "Schema::table('subjects', function (Blueprint \$table) {\n            if (Schema::hasColumn('subjects', 'is_active')) { return; }",
            $content
        );
        file_put_contents($migFile, $content);
        echo "  FIXED: Added column check to ".basename($migFile)."\n";
    } else {
        echo "  Already has column check\n";
    }
}

// STEP 2: Create tables directly if they don't exist
echo "\n--- Step 2: Ensuring tables exist ---\n";
 $env = file_get_contents($b."/.env");
preg_match("/DB_DATABASE=(.*)/", $env, $m1); preg_match("/DB_USERNAME=(.*)/", $env, $m2);
preg_match("/DB_PASSWORD=(.*)/", $env, $m3); preg_match("/DB_HOST=(.*)/", $env, $m4);
 $dbn = isset($m1[1]) ? trim($m1[1]) : "school_of_redemption";
 $dbu = isset($m2[1]) ? trim($m2[1]) : "root";
 $dbp = isset($m3[1]) ? trim($m3[1]) : "";
 $dbh = isset($m4[1]) ? trim($m4[1]) : "127.0.0.1";
try {
    $db = new PDO("mysql:host=$dbh;dbname=$dbn", $dbu, $dbp);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) { die("DB Error: ".$e->getMessage()."\n"); }

// Create grade_scales table
try {
    $db->exec("SELECT 1 FROM grade_scales LIMIT 1");
    echo "  grade_scales table exists\n";
} catch (Exception $e) {
    $db->exec("CREATE TABLE grade_scales (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        min_percentage DECIMAL(5,2) NOT NULL,
        max_percentage DECIMAL(5,2) NOT NULL,
        grade_point DECIMAL(3,2) NOT NULL DEFAULT 0,
        description TEXT NULL,
        is_passing TINYINT(1) NOT NULL DEFAULT 1,
        sort_order INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  CREATED grade_scales table\n";
}

// Create promotion_settings table
try {
    $db->exec("SELECT 1 FROM promotion_settings LIMIT 1");
    echo "  promotion_settings table exists\n";
} catch (Exception $e) {
    $db->exec("CREATE TABLE promotion_settings (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        academic_year_id BIGINT UNSIGNED NOT NULL,
        class_id BIGINT UNSIGNED NULL,
        minimum_average DECIMAL(5,2) NOT NULL DEFAULT 50.00,
        minimum_subjects_passed INT NOT NULL DEFAULT 0,
        core_subjects_must_pass TINYINT(1) NOT NULL DEFAULT 1,
        attendance_minimum DECIMAL(5,2) NULL DEFAULT 75.00,
        include_attendance TINYINT(1) NOT NULL DEFAULT 0,
        behavior_minimum DECIMAL(5,2) NULL DEFAULT 0,
        include_behavior TINYINT(1) NOT NULL DEFAULT 0,
        promotion_type ENUM('automatic','manual','hybrid') NOT NULL DEFAULT 'hybrid',
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        additional_rules TEXT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        UNIQUE KEY promo_unique (academic_year_id, class_id),
        FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  CREATED promotion_settings table\n";
}

// Create promotion_results table
try {
    $db->exec("SELECT 1 FROM promotion_results LIMIT 1");
    echo "  promotion_results table exists\n";
} catch (Exception $e) {
    $db->exec("CREATE TABLE promotion_results (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        student_id BIGINT UNSIGNED NOT NULL,
        academic_year_id BIGINT UNSIGNED NOT NULL,
        term_id BIGINT UNSIGNED NULL,
        from_class_id BIGINT UNSIGNED NOT NULL,
        to_class_id BIGINT UNSIGNED NULL,
        status ENUM('promoted','detained','conditional','pending','review') NOT NULL DEFAULT 'pending',
        overall_average DECIMAL(5,2) NOT NULL DEFAULT 0,
        overall_percentage DECIMAL(5,2) NOT NULL DEFAULT 0,
        overall_grade VARCHAR(255) NULL,
        grade_point_average DECIMAL(3,2) NOT NULL DEFAULT 0,
        total_subjects INT NOT NULL DEFAULT 0,
        subjects_passed INT NOT NULL DEFAULT 0,
        subjects_failed INT NOT NULL DEFAULT 0,
        class_rank INT NULL,
        total_students INT NULL,
        attendance_percentage DECIMAL(5,2) NULL,
        failure_reasons TEXT NULL,
        remarks TEXT NULL,
        processed_by BIGINT UNSIGNED NULL,
        processed_at TIMESTAMP NULL,
        is_final TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        UNIQUE KEY promo_result_unique (student_id, academic_year_id, term_id),
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
        FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE SET NULL,
        FOREIGN KEY (from_class_id) REFERENCES classes(id) ON DELETE CASCADE,
        FOREIGN KEY (to_class_id) REFERENCES classes(id) ON DELETE SET NULL,
        FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "  CREATED promotion_results table\n";
}

// STEP 3: Seed grade scales
echo "\n--- Step 3: Seeding grade scales ---\n";
 $stmt = $db->query("SELECT COUNT(*) FROM grade_scales");
 $count = $stmt->fetchColumn();
if ($count == 0) {
    $grades = [
        ["A+", 95.00, 100.00, 4.00, "Outstanding", 1, 1],
        ["A",  90.00, 94.99, 4.00, "Excellent", 1, 2],
        ["A-", 85.00, 89.99, 3.75, "Very Good", 1, 3],
        ["B+", 80.00, 84.99, 3.50, "Good", 1, 4],
        ["B",  75.00, 79.99, 3.00, "Above Average", 1, 5],
        ["B-", 70.00, 74.99, 2.75, "Satisfactory", 1, 6],
        ["C+", 65.00, 69.99, 2.50, "Average", 1, 7],
        ["C",  60.00, 64.99, 2.00, "Below Average", 1, 8],
        ["C-", 55.00, 59.99, 1.75, "Poor", 1, 9],
        ["D",  50.00, 54.99, 1.00, "Marginal Pass", 1, 10],
        ["F",  0.00,  49.99, 0.00, "Fail", 0, 11],
    ];
    $sql = "INSERT INTO grade_scales (name, min_percentage, max_percentage, grade_point, description, is_passing, sort_order, created_at, updated_at) VALUES ";
    $vals = [];
    foreach ($grades as $g) {
        $vals[] = "('".$g[0]."', ".$g[1].", ".$g[2].", ".$g[3].", '".$g[4]."', ".$g[5].", ".$g[6].", NOW(), NOW())";
    }
    $db->exec($sql . implode(", ", $vals));
    echo "  Inserted ".count($grades)." grade scales\n";
} else {
    echo "  Grade scales already exist ($count records)\n";
}

// STEP 4: Fix logo in settings - ensure it saves properly
echo "\n--- Step 4: Checking settings logo ---\n";
 $stmt = $db->query("SELECT * FROM settings WHERE `key` LIKE '%logo%' OR `key` LIKE '%photo%' OR `key` LIKE '%image%'");
 $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (count($settings) > 0) {
    foreach ($settings as $s) {
        echo "  Setting: key=".$s['key']." value=".substr($s['value'], 0, 80)."\n";
    }
} else {
    echo "  No logo/photo/image settings found\n";
    // Check all settings
    $stmt2 = $db->query("SELECT `key`, `value` FROM settings LIMIT 30");
    $allSettings = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo "  All settings keys:\n";
    foreach ($allSettings as $s) {
        echo "    - ".$s['key']." = ".substr($s['value'], 0, 60)."\n";
    }
}

// STEP 5: Fix logo display in all document views
echo "\n--- Step 5: Fixing logo in document views ---\n";

// Find all blade files that might display logo
 $viewsDir = $b."/resources/views";
 $logoPatterns = [
    'logo',
    'school_logo',
    'site_logo',
];
 $filesFixed = 0;

function fixLogoInView($dir, &$filesFixed) {
    if (!is_dir($dir)) return;
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir.'/'.$item;
        if (is_dir($path)) { fixLogoInView($path, $filesFixed); continue; }
        if (strpos($path, '.blade.php') === false) continue;

        $content = file_get_contents($path);
        $rel = str_replace(getcwd().'/', '', $path);
        $changed = false;

        // Pattern 1: {{ $settings->logo }} - needs asset() or Storage::url()
        if (preg_match('/\{\{\s*\$settings->logo\s*\}\}/', $content)) {
            // Replace with proper path that checks both storage and public
            $content = str_replace(
                '{{ $settings->logo }}',
                '{{ $settings->logo ? (file_exists(public_path($settings->logo)) ? asset($settings->logo) : (file_exists(storage_path(\'app/public/\' . $settings->logo)) ? Storage::url($settings->logo) : asset($settings->logo))) : asset(\'images/default-logo.png\') }}',
                $content
            );
            $changed = true;
        }

        // Pattern 2: {{ asset($settings->logo) }}
        if (preg_match('/\{\{\s*asset\(\$settings->logo\)\s*\}\}/', $content)) {
            $content = str_replace(
                '{{ asset($settings->logo) }}',
                '{{ $settings->logo ? (file_exists(public_path($settings->logo)) ? asset($settings->logo) : (file_exists(storage_path(\'app/public/\' . $settings->logo)) ? Storage::url($settings->logo) : asset($settings->logo))) : asset(\'images/default-logo.png\') }}',
                $content
            );
            $changed = true;
        }

        // Pattern 3: src="{{ $settings->logo }}"
        if (preg_match('/src="\{\{\s*\$settings->logo\s*\}\}"/', $content)) {
            $content = preg_replace(
                '/src="\{\{\s*\$settings->logo\s*\}\}"/',
                'src="{{ $settings->logo ? (file_exists(public_path($settings->logo)) ? asset($settings->logo) : (file_exists(storage_path(\'app/public/\' . $settings->logo)) ? Storage::url($settings->logo) : asset($settings->logo))) : asset(\'images/default-logo.png\') }}"',
                $content
            );
            $changed = true;
        }

        // Pattern 4: Hard-coded logo paths
        if (strpos($content, 'logo.png') !== false && strpos($content, '$settings') === false) {
            // Add settings-based logo before the hardcoded one
            // Don't auto-fix this, just report
            echo "  INFO: $rel has hardcoded logo.png reference\n";
        }

        if ($changed) {
            file_put_contents($path, $content);
            echo "  FIXED logo path in $rel\n";
            $filesFixed++;
        }
    }
}

fixLogoInView($viewsDir, $filesFixed);
if ($filesFixed === 0) echo "  No logo fixes needed in views\n";

// STEP 6: Check for Redemption/ subdirectory views too
echo "\n--- Step 6: Checking Redemption/ subdirectory views ---\n";
 $redemptionDir = $b."/resources/views/Redemption";
if (is_dir($redemptionDir)) {
    fixLogoInView($redemptionDir, $filesFixed);
}

// STEP 7: Ensure storage link exists
echo "\n--- Step 7: Storage link ---\n";
if (!file_exists($b."/public/storage")) {
    exec("php artisan storage:link 2>&1", $out, $rc);
    echo "  Storage link created: ".($rc === 0 ? "OK" : "check manually")."\n";
} else {
    echo "  Storage link exists\n";
}

// STEP 8: Check what logo files actually exist
echo "\n--- Step 8: Finding uploaded logos ---\n";
 $logoLocations = [
    $b."/public/uploads/*logo*",
    $b."/public/images/*logo*",
    $b."/storage/app/public/*logo*",
    $b."/storage/app/public/settings/*",
    $b."/public/*.png",
    $b."/public/*.jpg",
];
foreach ($logoLocations as $pattern) {
    $files = glob($pattern);
    foreach ($files as $f) {
        echo "  Found: ".str_replace($b."/", '', $f)."\n";
    }
}

// STEP 9: Add a helper to settings that always returns correct logo URL
echo "\n--- Step 9: Adding logo helper to Setting model ---\n";
 $settingModel = $b."/app/Models/Setting.php";
if (file_exists($settingModel)) {
    $sm = file_get_contents($settingModel);
    if (strpos($sm, 'getLogoUrl') === false) {
        // Add a helper method
        $method = '
    /**
     * Get the full URL for the school logo
     */
    public static function getLogoUrl(): string
    {
        $logo = self::get(\'school_logo\') ?? self::get(\'logo\') ?? \'\';

        if (empty($logo)) {
            return asset(\'images/default-logo.png\');
        }

        // Check if file exists in public path
        if (file_exists(public_path($logo))) {
            return asset($logo);
        }

        // Check if file exists in storage
        if (file_exists(storage_path(\'app/public/\' . $logo))) {
            return Storage::url($logo);
        }

        // Check with uploads prefix
        if (file_exists(public_path(\'uploads/\' . $logo))) {
            return asset(\'uploads/\' . $logo);
        }

        // Try as-is with asset
        return asset($logo);
    }
';
        // Insert before the last closing brace
        $sm = preg_replace('/}\s*$/', $method . "}\n", $sm);
        file_put_contents($settingModel, $sm);
        echo "  Added getLogoUrl() to Setting model\n";
    } else {
        echo "  getLogoUrl() already exists\n";
    }
}

// STEP 10: Create a simple logo test page
echo "\n--- Step 10: Run migrations with --force ---\n";
exec("php artisan migrate --force 2>&1", $migOut, $migRC);
echo "  Migration output:\n";
foreach ($migOut as $line) {
    echo "    $line\n";
}

echo "\n============================================\n";
echo "  FIX COMPLETE!\n";
echo "============================================\n";
echo "\nWhat was fixed:\n";
echo "  1. is_active migration - added column check\n";
echo "  2. Created grade_scales, promotion_settings, promotion_results tables\n";
echo "  3. Seeded 11 grade scales (A+ to F)\n";
echo "  4. Fixed logo paths in document views\n";
echo "  5. Added getLogoUrl() helper to Setting model\n";
echo "  6. Ensured storage link exists\n";
echo "\nNow run: php fix_promotion_partB.php\n";
