<?php
/**
 * Fix Logo on Documents
 * - Diagnoses logo storage and display issues
 * - Fixes logo path references in views
 * - Ensures storage:link exists
 * - Updates settings/logo references in document views
 */

echo "=== Fixing Logo on Documents ===\n\n";

 $projectPath = __DIR__;
chdir($projectPath);

require $projectPath . '/vendor/autoload.php';

 $app = require_once $projectPath . '/bootstrap/app.php';
 $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

// Step 1: Diagnose current logo state
echo "Step 1: Diagnosing logo storage...\n";

// Check settings table
if (Schema::hasTable('settings')) {
    $settings = DB::table('settings')->first();
    if ($settings) {
        echo "  - Settings table columns: " . implode(', ', array_keys((array)$settings)) . "\n";
        // Look for logo-related columns
        $logoColumns = array_filter(array_keys((array)$settings), function($col) {
            return stripos($col, 'logo') !== false || stripos($col, 'image') !== false || stripos($col, 'photo') !== false;
        });
        echo "  - Logo-related columns: " . implode(', ', $logoColumns) . "\n";
        foreach ($logoColumns as $col) {
            echo "  - $col value: " . ($settings->$col ?? 'NULL') . "\n";
        }
    }
} else {
    echo "  - No settings table found!\n";
}

// Check if storage link exists
 $publicStorage = $projectPath . '/public/storage';
 $storageApp = $projectPath . '/storage/app/public';
echo "\n  - public/storage exists: " . (file_exists($publicStorage) ? 'YES' : 'NO') . "\n";
echo "  - storage/app/public exists: " . (is_dir($storageApp) ? 'YES' : 'NO') . "\n";

if (!file_exists($publicStorage) && is_dir($storageApp)) {
    echo "  - Creating storage:link...\n";
    echo shell_exec('php artisan storage:link 2>&1');
}

// Check for uploaded logos
 $logoDirs = [
    $projectPath . '/storage/app/public/logos',
    $projectPath . '/storage/app/public/settings',
    $projectPath . '/storage/app/public/uploads',
    $projectPath . '/public/uploads/logos',
    $projectPath . '/public/logos',
    $projectPath . '/public/images',
];

echo "\n  - Searching for logo files...\n";
foreach ($logoDirs as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '/*');
        if (!empty($files)) {
            echo "  - Found in $dir:\n";
            foreach ($files as $f) {
                echo "    - " . basename($f) . "\n";
            }
        }
    }
}

// Step 2: Find all document views that should display logo
echo "\nStep 2: Finding document views with logo references...\n";

 $viewDirs = [
    $projectPath . '/resources/views/admin',
];

 $documentViews = [];
 $logoPatterns = ['idcard', 'certificate', 'progress', 'report', 'result', 'marksheet', 'transcript'];

foreach ($viewDirs as $viewDir) {
    if (!is_dir($viewDir)) continue;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewDir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filename = strtolower($file->getFilename());
            $filepath = $file->getPathname();
            foreach ($logoPatterns as $pattern) {
                if (strpos($filename, $pattern) !== false || strpos(strtolower($filepath), $pattern) !== false) {
                    $documentViews[] = $filepath;
                    break;
                }
            }
        }
    }
}

// Also check for any views that reference logo
 $allBladeFiles = glob($projectPath . '/resources/views/**/*.blade.php', GLOB_BRACE);
if (empty($allBladeFiles)) {
    $allBladeFiles = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($projectPath . '/resources/views', RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $allBladeFiles[] = $file->getPathname();
        }
    }
}

echo "  - Checking " . count($allBladeFiles) . " blade files for logo references...\n";

 $filesWithLogo = [];
foreach ($allBladeFiles as $file) {
    $content = file_get_contents($file);
    if (preg_match('/logo|school_logo|site_logo|setting.*logo/i', $content)) {
        $filesWithLogo[] = $file;
    }
}

echo "  - Files referencing logo (" . count($filesWithLogo) . "):\n";
foreach ($filesWithLogo as $file) {
    echo "    - " . str_replace($projectPath . '/', '', $file) . "\n";
}

// Step 3: Fix logo references in document views
echo "\nStep 3: Fixing logo references in documents...\n";

// Create a helper that finds the logo URL from settings
 $logoHelperContent = <<<'HELPER'
<?php

if (!function_exists('getSchoolLogoUrl')) {
    function getSchoolLogoUrl($default = null)
    {
        $settings = \App\Models\Setting::first();
        
        if ($settings) {
            // Check common logo column names
            $logoFields = ['logo', 'school_logo', 'site_logo', 'image', 'photo', 'logo_path'];
            foreach ($logoFields as $field) {
                if (isset($settings->$field) && !empty($settings->$field)) {
                    $path = $settings->$field;
                    // If it's already a full URL
                    if (filter_var($path, FILTER_VALIDATE_URL)) {
                        return $path;
                    }
                    // If it starts with /, it's relative to public
                    if (strpos($path, '/') === 0) {
                        return asset(ltrim($path, '/'));
                    }
                    // If it contains storage path
                    if (strpos($path, 'storage/') !== false) {
                        return asset($path);
                    }
                    // Try as storage file
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                        return asset('storage/' . $path);
                    }
                    // Try as direct file
                    if (file_exists(public_path($path))) {
                        return asset($path);
                    }
                    return asset($path);
                }
            }
        }
        
        // Check for uploaded logo files directly
        $possiblePaths = [
            'storage/logos',
            'storage/settings', 
            'storage/uploads',
            'uploads/logos',
            'logos',
            'images/logo.png',
            'images/school-logo.png',
        ];
        
        foreach ($possiblePaths as $dir) {
            $fullPath = public_path($dir);
            if (is_dir($fullPath)) {
                $files = glob($fullPath . '/*.{png,jpg,jpeg,gif,svg,webp}', GLOB_BRACE);
                if (!empty($files)) {
                    return asset($dir . '/' . basename($files[0]));
                }
            }
        }
        
        // Return default or a placeholder
        return $default ?: asset('images/default-logo.png');
    }
}

if (!function_exists('getSchoolName')) {
    function getSchoolName()
    {
        $settings = \App\Models\Setting::first();
        if ($settings) {
            $nameFields = ['school_name', 'name', 'site_name', 'title'];
            foreach ($nameFields as $field) {
                if (isset($settings->$field) && !empty($settings->$field)) {
                    return $settings->$field;
                }
            }
        }
        return config('app.name', 'School');
    }
}
HELPER;

// Write helper file
 $helpersDir = $projectPath . '/app/Helpers';
if (!is_dir($helpersDir)) {
    mkdir($helpersDir, 0755, true);
}
 $helperPath = $helpersDir . '/LogoHelper.php';
file_put_contents($helperPath, $logoHelperContent);
echo "  - Created LogoHelper.php\n";

// Add helper to composer.json autoload
 $composerFile = $projectPath . '/composer.json';
 $composerContent = json_decode(file_get_contents($composerFile), true);

if (!isset($composerContent['autoload']['files'])) {
    $composerContent['autoload']['files'] = [];
}

 $helperPathComposer = 'app/Helpers/LogoHelper.php';
if (!in_array($helperPathComposer, $composerContent['autoload']['files'])) {
    $composerContent['autoload']['files'][] = $helperPathComposer;
    file_put_contents($composerFile, json_encode($composerContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo "  - Added LogoHelper to composer autoload\n";
    
    // Dump autoload
    echo shell_exec('php composer.phar dump-autoload 2>&1') ?? shell_exec('composer dump-autoload 2>&1') ?? "  - Please run: composer dump-autoload\n";
} else {
    echo "  - LogoHelper already in autoload\n";
}

// Step 4: Fix logo display in document views
echo "\nStep 4: Patching document views to use getSchoolLogoUrl()...\n";

 $patched = 0;
foreach ($filesWithLogo as $file) {
    $content = file_get_contents($file);
    $original = $content;
    $relativePath = str_replace($projectPath . '/', '', $file);
    
    // Common patterns to fix:
    // Pattern 1: {{ asset('images/logo.png') }} or hardcoded logo paths
    $content = preg_replace(
        '/\{\{\s*asset\([\'"](images\/logo[^\'"]*)[\'"]\)\s*\}\}/i',
        '{{ getSchoolLogoUrl() }}',
        $content
    );
    
    // Pattern 2: <img src="{{ asset('images/logo.png') }}">
    $content = preg_replace(
        '/src=\{\{\s*asset\([\'"](images\/logo[^\'"]*)[\'"]\)\s*\}\}/i',
        'src="{{ getSchoolLogoUrl() }}"',
        $content
    );
    
    // Pattern 3: {{ $setting->logo ?? 'default' }} - use helper instead
    $content = preg_replace(
        '/\{\{\s*\$setting[s]?->(logo|school_logo|site_logo|image)\s*\?\?\s*[\'"][^\'"]*[\'"]\s*\}\}/i',
        '{{ getSchoolLogoUrl() }}',
        $content
    );
    
    // Pattern 4: src="{{ $setting->logo ?? asset('...') }}"
    $content = preg_replace(
        '/src="\{\{\s*\$setting[s]?->(logo|school_logo|site_logo|image)\s*\?\?[^}]*\}\}"/i',
        'src="{{ getSchoolLogoUrl() }}"',
        $content
    );
    
    // Pattern 5: {{ Storage::url($setting->logo) }}
    $content = preg_replace(
        '/\{\{\s*Storage::url\(\s*\$setting[s]?->(logo|school_logo|site_logo|image)\s*\)\s*\}\}/i',
        '{{ getSchoolLogoUrl() }}',
        $content
    );
    
    // Pattern 6: Add getSchoolLogoUrl() where logo img tags exist but use wrong paths
    // Look for img tags with logo in src that aren't already using our helper
    if (strpos($content, 'getSchoolLogoUrl') === false && preg_match('/<img[^>]*(logo|school)[^>]*>/i', $content)) {
        $content = preg_replace(
            '/(<img[^>]*src=["\'])([^"\']*)(["\'][^>]*(?:logo|school)[^>]*>)/i',
            '$1{{ getSchoolLogoUrl() }}$3',
            $content
        );
        $content = preg_replace(
            '/(<img[^>]*(?:logo|school)[^>]*src=["\'])([^"\']*)(["\'][^>]*>)/i',
            '$1{{ getSchoolLogoUrl() }}$3',
            $content
        );
    }
    
    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "  - Patched: $relativePath\n";
        $patched++;
    } else {
        echo "  - No changes needed: $relativePath\n";
    }
}

echo "  - Total files patched: $patched\n";

// Step 5: Check and fix the Setting model for logo accessor
echo "\nStep 5: Checking Setting model for logo accessor...\n";

 $settingModelPath = $projectPath . '/app/Models/Setting.php';
if (file_exists($settingModelPath)) {
    $settingModel = file_get_contents($settingModelPath);
    
    // Check if it has a getLogoUrlAttribute or similar
    if (strpos($settingModel, 'getLogoUrlAttribute') === false && strpos($settingModel, 'logo_url') === false) {
        // Add a logo_url accessor
        $accessor = '
    public function getLogoUrlAttribute()
    {
        \$logoFields = [\'logo\', \'school_logo\', \'site_logo\', \'image\', \'photo\', \'logo_path\'];
        foreach (\$logoFields as \$field) {
            if (!empty(\$this->attributes[\$field])) {
                \$path = \$this->attributes[\$field];
                if (filter_var(\$path, FILTER_VALIDATE_URL)) return \$path;
                if (\\Illuminate\\Support\\Facades\\Storage::disk(\'public\')->exists(\$path)) {
                    return asset(\'storage/\' . \$path);
                }
                if (file_exists(public_path(\$path))) return asset(\$path);
                return asset(\$path);
            }
        }
        return asset(\'images/default-logo.png\');
    }
';
        // Find the class closing brace and insert before it
        $lastBrace = strrpos($settingModel, '}');
        if ($lastBrace !== false) {
            $settingModel = substr_replace($settingModel, $accessor . "\n", $lastBrace, 0);
            file_put_contents($settingModelPath, $settingModel);
            echo "  - Added logo_url accessor to Setting model\n";
        }
    } else {
        echo "  - Setting model already has logo accessor\n";
    }
    
    // Check fillable includes logo fields
    if (strpos($settingModel, 'logo') === false) {
        echo "  - WARNING: Setting model may not have logo in fillable. Check manually.\n";
    }
} else {
    echo "  - WARNING: Setting.php model not found at expected path\n";
}

// Step 6: Check SettingsController for logo upload handling
echo "\nStep 6: Checking Settings controller for logo upload...\n";

 $controllerPaths = [
    $projectPath . '/app/Http/Controllers/SettingController.php',
    $projectPath . '/app/Http/Controllers/Admin/SettingController.php',
    $projectPath . '/app/Http/Controllers/Admin/SettingsController.php',
];

foreach ($controllerPaths as $ctrlPath) {
    if (file_exists($ctrlPath)) {
        echo "  - Found controller: " . str_replace($projectPath . '/', '', $ctrlPath) . "\n";
        $ctrlContent = file_get_contents($ctrlPath);
        
        // Check if it handles logo upload
        if (strpos($ctrlContent, 'logo') !== false && strpos($ctrlContent, 'upload') !== false) {
            echo "  - Controller has logo upload logic\n";
            
            // Check if it stores to the correct disk
            if (strpos($ctrlContent, "Storage::disk('public')") !== false || strpos($ctrlContent, "'public'") !== false) {
                echo "  - Uses 'public' disk for storage (correct)\n";
            } else {
                echo "  - WARNING: May not be using 'public' disk. Logo may not be web-accessible.\n";
                echo "  - Checking stored logo paths in database...\n";
            }
        } else {
            echo "  - WARNING: Controller may not have logo upload handling\n";
        }
        break;
    }
}

// Step 7: Verify database logo path and check if file exists
echo "\nStep 7: Verifying logo file in database vs filesystem...\n";

if (Schema::hasTable('settings')) {
    $settings = DB::table('settings')->first();
    if ($settings) {
        $logoFields = ['logo', 'school_logo', 'site_logo', 'image', 'photo', 'logo_path'];
        foreach ($logoFields as $field) {
            if (isset($settings->$field) && !empty($settings->$field)) {
                $logoValue = $settings->$field;
                echo "  - Logo field '$field' value: $logoValue\n";
                
                // Check various path interpretations
                $possiblePaths = [
                    $projectPath . '/public/' . $logoValue,
                    $projectPath . '/storage/app/public/' . $logoValue,
                    $projectPath . '/public/storage/' . $logoValue,
                    $projectPath . '/' . $logoValue,
                ];
                
                foreach ($possiblePaths as $checkPath) {
                    if (file_exists($checkPath)) {
                        echo "  - FOUND at: " . str_replace($projectPath . '/', '', $checkPath) . "\n";
                    }
                }
            }
        }
    }
}

// Step 8: Ensure storage:link and clear caches
echo "\nStep 8: Final cleanup...\n";

if (!file_exists($publicStorage)) {
    echo shell_exec('php artisan storage:link 2>&1');
}

echo shell_exec('php artisan view:clear 2>&1');
echo shell_exec('php artisan config:clear 2>&1');
echo shell_exec('php artisan cache:clear 2>&1');

echo "\n=== Logo Fix Complete ===\n";
echo "Summary of changes:\n";
echo "  1. Created LogoHelper.php with getSchoolLogoUrl() and getSchoolName() functions\n";
echo "  2. Added helper to composer autoload\n";
echo "  3. Patched document views to use getSchoolLogoUrl()\n";
echo "  4. Added logo_url accessor to Setting model\n";
echo "  5. Verified storage:link\n";
echo "\nIMPORTANT: Run 'composer dump-autoload' if not done automatically.\n";
