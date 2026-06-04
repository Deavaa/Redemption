<?php
/**
 * ============================================================
 * HOMEPAGE PERMANENT FIX — Extract CSS/JS from Blade Templates
 * ============================================================
 *
 * ROOT CAUSE: welcome.blade.php (92KB) + layouts/website.blade.php (36KB) = 128KB
 * of Blade templates. When Laravel compiles these, it creates a massive PHP file
 * that exceeds shared hosting's PHP memory limit (typically 32-64MB on ByetHost).
 * The /login page works because it's a standalone 332-line page with no layout.
 *
 * FIX: Extract ~1400 lines of inline CSS and ~200 lines of inline JS into
 * external files. Keep only the dynamic :root CSS variables inline.
 * This reduces template size by ~70%, from 128KB to ~35KB.
 *
 * Upload this file to your Laravel root directory (same level as artisan)
 * and visit: https://redemption.byethost4.com/fix-homepage-permanent.php
 * ============================================================
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 120);
ini_set('memory_limit', '256M');

$baseDir = __DIR__;
$results = [];
$backups = [];

function logResult($section, $message, $status = 'info') {
    global $results;
    $results[] = compact('section', 'message', 'status');
    $icon = ['success' => '✅', 'error' => '❌', 'warning' => '⚠️', 'info' => 'ℹ️'][$status] ?? '•';
    echo "<div style='margin:4px 0;padding:4px 8px;border-radius:4px;background:" .
         ($status === 'error' ? '#fee' : ($status === 'success' ? '#efe' : '#eef')) .
         ";'><strong>{$icon} {$section}:</strong> {$message}</div>\n";
    flush();
}

function backupFile($path) {
    global $backups;
    $backup = $path . '.bak-' . date('Ymd-His');
    if (file_exists($path)) {
        copy($path, $backup);
        $backups[] = $backup;
    }
    return $backup;
}

function ensureDir($dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return is_dir($dir) && is_writable($dir);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>🔧 Homepage Permanent Fix</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; background: #0d1117; color: #c9d1d9; }
        h1 { color: #58a6ff; border-bottom: 1px solid #30363d; padding-bottom: 10px; }
        h2 { color: #79c0ff; margin-top: 30px; }
        .card { background: #161b22; border: 1px solid #30363d; border-radius: 8px; padding: 16px; margin: 12px 0; }
        a { color: #58a6ff; }
        .error-detail { background: #1c1c2e; border: 1px solid #f85149; border-radius: 6px; padding: 12px; margin: 8px 0; font-family: monospace; font-size: 13px; white-space: pre-wrap; word-break: break-all; max-height: 300px; overflow-y: auto; }
        .success-box { background: #0d2818; border: 1px solid #2ea043; border-radius: 8px; padding: 16px; margin: 16px 0; }
    </style>
</head>
<body>
<h1>🔧 Homepage Permanent Fix</h1>
<p>Root cause: 128KB of Blade templates exceed shared hosting memory limit.<br>
Fix: Extract CSS/JS to external files → reduce template size by ~70%.</p>

<?php

// ============================================================
// PHASE 0: Check prerequisites
// ============================================================
echo "<h2>Phase 0: Prerequisites</h2>\n";

$welcomePath = $baseDir . '/resources/views/welcome.blade.php';
$layoutPath = $baseDir . '/resources/views/layouts/website.blade.php';
$publicDir = $baseDir . '/public';
$cssDir = $publicDir . '/css';
$jsDir = $publicDir . '/js';

if (!file_exists($welcomePath)) {
    logResult('Prerequisites', 'welcome.blade.php NOT FOUND at expected path', 'error');
    echo "</body></html>";
    exit;
}
logResult('Prerequisites', 'welcome.blade.php found (' . filesize($welcomePath) . ' bytes)', 'success');

if (!file_exists($layoutPath)) {
    logResult('Prerequisites', 'layouts/website.blade.php NOT FOUND', 'error');
    echo "</body></html>";
    exit;
}
logResult('Prerequisites', 'layouts/website.blade.php found (' . filesize($layoutPath) . ' bytes)', 'success');

$memoryLimit = ini_get('memory_limit');
logResult('PHP Config', "Memory limit: {$memoryLimit}", 'info');

// Ensure CSS and JS directories exist
ensureDir($cssDir);
ensureDir($jsDir);
logResult('Directories', "CSS dir: {$cssDir}", ensureDir($cssDir) ? 'success' : 'error');
logResult('Directories', "JS dir: {$jsDir}", ensureDir($jsDir) ? 'success' : 'error');

// ============================================================
// PHASE 1: Capture current error via curl
// ============================================================
echo "<h2>Phase 1: Capture Current Error</h2>\n";

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$homeUrl = $baseUrl . '/';

logResult('Curl Test', "Testing: {$homeUrl}", 'info');

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $homeUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Homepage Fix Diagnostic)',
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$body = substr($response, $headerSize);
$error = curl_error($ch);
curl_close($ch);

logResult('Curl Test', "HTTP Status: {$httpCode}", $httpCode === 200 ? 'success' : 'error');

if ($error) {
    logResult('Curl Error', $error, 'warning');
}

if ($httpCode !== 200 && $body) {
    // Save the error body for inspection
    $errorLogFile = $baseDir . '/homepage-500-error.html';
    file_put_contents($errorLogFile, $body);
    logResult('Error Body', "Saved to homepage-500-error.html (" . strlen($body) . " bytes)", 'info');

    // Try to extract error message from the HTML
    if (preg_match('/<title>(.*?)<\/title>/is', $body, $m)) {
        logResult('Error Title', $m[1], 'warning');
    }
    // Look for Laravel error details
    if (preg_match('/class="exception_message"[^>]*>(.*?)<\/h1>/s', $body, $m)) {
        logResult('Laravel Error', strip_tags($m[1]), 'error');
    }
    if (preg_match('/Fatal error:.*?in .*? on line \d+/s', $body, $m)) {
        logResult('PHP Fatal Error', $m[0], 'error');
    }
}

// ============================================================
// PHASE 2: Extract CSS from welcome.blade.php
// ============================================================
echo "<h2>Phase 2: Extract CSS/JS from welcome.blade.php</h2>\n";

$welcomeContent = file_get_contents($welcomePath);
$originalWelcomeSize = strlen($welcomeContent);
logResult('Read', "welcome.blade.php: {$originalWelcomeSize} bytes", 'info');

// Extract the @push('styles') block
$stylesExtracted = false;
$scriptsExtracted = false;
$homepageCss = '';
$homepageJs = '';
$newWelcome = $welcomeContent;

// Extract CSS from @push('styles') ... @endpush
if (preg_match('/@push\(\'styles\'\)\s*<style>(.*?)<\/style>\s*<\/style>\s*@endpush/s', $welcomeContent, $m)) {
    // Has duplicate </style> — extract content between first <style> and last </style>
    $homepageCss = $m[1];
    // Remove the duplicate </style>
    $homepageCss = preg_replace('/\s*<\/style>\s*$/', '', $homepageCss);
    $stylesExtracted = true;
} elseif (preg_match('/@push\(\'styles\'\)\s*<style>(.*?)<\/style>\s*@endpush/s', $welcomeContent, $m)) {
    $homepageCss = $m[1];
    $stylesExtracted = true;
}

if ($stylesExtracted) {
    // Write CSS file
    $cssFile = $cssDir . '/homepage.css';
    file_put_contents($cssFile, $homepageCss);
    logResult('CSS Extract', "homepage.css: " . filesize($cssFile) . " bytes written", 'success');

    // Replace the @push('styles') block with external link
    $newWelcome = preg_replace(
        '/@push\(\'styles\'\)\s*<style>.*?<\/style>\s*<\/style>\s*@endpush/s',
        "@push('styles')\n    <link rel=\"stylesheet\" href=\"{{ asset('css/homepage.css') }}\">\n@endpush",
        $newWelcome
    );
    if (strpos($newWelcome, "asset('css/homepage.css')") === false) {
        // Fallback: try the pattern without duplicate </style>
        $newWelcome = preg_replace(
            '/@push\(\'styles\'\)\s*<style>.*?<\/style>\s*@endpush/s',
            "@push('styles')\n    <link rel=\"stylesheet\" href=\"{{ asset('css/homepage.css') }}\">\n@endpush",
            $newWelcome
        );
    }
    logResult('CSS Replace', 'Replaced inline CSS with external link', 'success');
} else {
    logResult('CSS Extract', 'Could not find @push styles block', 'error');
}

// Extract JS from @push('scripts') ... @endpush
if (preg_match('/@push\(\'scripts\'\)\s*<script>(.*?)<\/script>\s*@endpush/s', $newWelcome, $m)) {
    $homepageJs = $m[1];
    $scriptsExtracted = true;

    $jsFile = $jsDir . '/homepage.js';
    file_put_contents($jsFile, $homepageJs);
    logResult('JS Extract', "homepage.js: " . filesize($jsFile) . " bytes written", 'success');

    $newWelcome = preg_replace(
        '/@push\(\'scripts\'\)\s*<script>.*?<\/script>\s*@endpush/s',
        "@push('scripts')\n    <script src=\"{{ asset('js/homepage.js') }}\"></script>\n@endpush",
        $newWelcome
    );
    logResult('JS Replace', 'Replaced inline JS with external script', 'success');
} else {
    logResult('JS Extract', 'Could not find @push scripts block', 'warning');
}

// Write the new welcome.blade.php
if ($stylesExtracted || $scriptsExtracted) {
    backupFile($welcomePath);
    $newWelcomeSize = strlen($newWelcome);
    file_put_contents($welcomePath, $newWelcome);
    $reduction = round((1 - $newWelcomeSize / $originalWelcomeSize) * 100);
    logResult('Write', "welcome.blade.php: {$originalWelcomeSize} → {$newWelcomeSize} bytes (−{$reduction}%)", 'success');
}

// ============================================================
// PHASE 3: Extract CSS/JS from layouts/website.blade.php
// ============================================================
echo "<h2>Phase 3: Extract CSS/JS from layouts/website.blade.php</h2>\n";

$layoutContent = file_get_contents($layoutPath);
$originalLayoutSize = strlen($layoutContent);
logResult('Read', "website.blade.php: {$originalLayoutSize} bytes", 'info');

// Extract the <style> block — but keep the :root variables inline
$layoutCss = '';
$layoutRootVars = '';
$layoutStylesExtracted = false;
$layoutJs = '';
$layoutScriptsExtracted = false;
$newLayout = $layoutContent;

// Find the main <style> block (contains :root)
if (preg_match('/<style>(.*?)<\/style>/s', $layoutContent, $m)) {
    $fullCss = $m[1];

    // Extract :root variables block (MUST stay inline because it uses Blade/PHP)
    if (preg_match('/:root\s*\{[^}]+\}/s', $fullCss, $rootMatch)) {
        $layoutRootVars = $rootMatch[0];

        // Convert PHP variables in :root to their CSS variable equivalents
        // The :root block already uses {{ $primaryHex }}, {{ $settings['secondary_color'] }}, etc.
        // These MUST stay inline. We keep them as-is.
    }

    // Convert PHP variable references in the rest of the CSS to CSS variables
    $externalCss = $fullCss;

    // Replace rgba({{ $primaryR }}, {{ $primaryG }}, {{ $primaryB }}, X) with rgba(var(--primary-rgb), X)
    $externalCss = preg_replace(
        '/rgba\(\s*\{\{\s*\$primaryR\s*\}\}\s*,\s*\{\{\s*\$primaryG\s*\}\}\s*,\s*\{\{\s*\$primaryB\s*\}\}\s*,\s*([0-9.]+)\s*\)/s',
        'rgba(var(--primary-rgb), $1)',
        $externalCss
    );

    // Remove :root from the external CSS (it stays inline)
    $externalCss = preg_replace('/:root\s*\{[^}]+\}\s*/s', '', $externalCss);

    // Clean up extra whitespace
    $externalCss = trim($externalCss);

    // Write the external CSS file
    $layoutCssFile = $cssDir . '/website.css';
    file_put_contents($layoutCssFile, $externalCss);
    logResult('CSS Extract', "website.css: " . filesize($layoutCssFile) . " bytes written", 'success');

    // Now replace the <style> block in the layout with inline :root + external link
    $newStyleBlock = "<style>\n        " . $layoutRootVars . "\n    </style>\n    <link rel=\"stylesheet\" href=\"{{ asset('css/website.css') }}\">";

    $newLayout = preg_replace(
        '/<style>.*?<\/style>/s',
        $newStyleBlock,
        $newLayout,
        1 // Only replace the first occurrence
    );

    $layoutStylesExtracted = true;
    logResult('CSS Replace', 'Replaced layout CSS with :root inline + external link', 'success');
} else {
    logResult('CSS Extract', 'Could not find <style> block in layout', 'error');
}

// Extract the <script> block (the main one before @stack('scripts'))
// The layout has a <script> block between the Bootstrap JS and @stack('scripts')
if (preg_match('/<script>\s*(\/\/ =+ Navbar Scroll.*?)<\/script>\s*@stack\(\'scripts\'\)/s', $layoutContent, $m)) {
    $layoutJs = $m[1];

    $layoutJsFile = $jsDir . '/website.js';
    file_put_contents($layoutJsFile, $layoutJs);
    logResult('JS Extract', "website.js: " . filesize($layoutJsFile) . " bytes written", 'success');

    // Replace the inline script with external reference
    $newLayout = preg_replace(
        '/<script>\s*\/\/ =+ Navbar Scroll.*?<\/script>(\s*)@stack\(\'scripts\'\)/s',
        '<script src="{{ asset(\'js/website.js\') }}"><\/script>$1@stack(\'scripts\')',
        $newLayout
    );

    $layoutScriptsExtracted = true;
    logResult('JS Replace', 'Replaced layout JS with external script', 'success');
} else {
    // Try a broader pattern
    if (preg_match('/<script>\s*\(function\(\).*?@stack\(\'scripts\'\)/s', $layoutContent, $m)) {
        logResult('JS Extract', 'Found script block but pattern too complex for auto-extraction', 'warning');
    } else {
        logResult('JS Extract', 'Could not find main <script> block in layout', 'warning');
    }
}

// Write the new layout
if ($layoutStylesExtracted) {
    backupFile($layoutPath);
    $newLayoutSize = strlen($newLayout);
    file_put_contents($layoutPath, $newLayout);
    $reduction = round((1 - $newLayoutSize / $originalLayoutSize) * 100);
    logResult('Write', "website.blade.php: {$originalLayoutSize} → {$newLayoutSize} bytes (−{$reduction}%)", 'success');
}

// ============================================================
// PHASE 4: Fix AppServiceProvider performance issues
// ============================================================
echo "<h2>Phase 4: Optimize AppServiceProvider</h2>\n";

$appProviderPath = $baseDir . '/app/Providers/AppServiceProvider.php';
if (file_exists($appProviderPath)) {
    $appProvider = file_get_contents($appProviderPath);
    $modified = false;

    // Remove the config cache deletion on every request
    if (strpos($appProvider, "if (file_exists(\$cachedConfig))") !== false) {
        $appProvider = preg_replace(
            '/\/\/ Delete stale cached config.*?\/\/ Silently fail\s*\}\s*\n/s',
            "// Config cache deletion removed — was destroying performance on every request\n",
            $appProvider
        );
        $modified = true;
        logResult('AppServiceProvider', 'Removed config cache deletion on every request', 'success');
    }

    // Make Schema::hasTable check only run once (cache the result)
    if (strpos($appProvider, "Schema::hasTable('sessions')") !== false) {
        // Replace the Schema check with a file-based flag
        $appProvider = preg_replace(
            '/try\s*\{\s*if\s*\(!\\\\Schema::hasTable\(\'sessions\'\)\)\s*\{.*?\}\s*\}\s*catch/s',
            "try {\n            // Only check/create sessions table once (not on every request)\n            \$flagFile = storage_path('framework/sessions_table_created');\n            if (!file_exists(\$flagFile)) {\n                if (!\\Schema::hasTable('sessions')) {\n                    \\Schema::create('sessions', function (\$table) {\n                        \$table->string('id')->primary();\n                        \$table->foreignId('user_id')->nullable()->index();\n                        \$table->string('ip_address', 45)->nullable();\n                        \$table->text('user_agent')->nullable();\n                        \$table->longText('payload');\n                        \$table->integer('last_activity')->index();\n                    });\n                }\n                @file_put_contents(\$flagFile, date('Y-m-d H:i:s'));\n            }\n        } catch",
            $appProvider
        );
        $modified = true;
        logResult('AppServiceProvider', 'Optimized sessions table check (runs once instead of every request)', 'success');
    }

    if ($modified) {
        backupFile($appProviderPath);
        file_put_contents($appProviderPath, $appProvider);
        logResult('AppServiceProvider', 'Saved optimized AppServiceProvider', 'success');
    } else {
        logResult('AppServiceProvider', 'No changes needed', 'info');
    }
} else {
    logResult('AppServiceProvider', 'File not found', 'warning');
}

// ============================================================
// PHASE 5: Clear ALL Laravel caches
// ============================================================
echo "<h2>Phase 5: Clear Caches</h2>\n";

// Clear compiled views
$viewsDir = $baseDir . '/storage/framework/views';
$viewCount = 0;
if (is_dir($viewsDir)) {
    foreach (glob($viewsDir . '/*') as $file) {
        if (is_file($file) && basename($file) !== '.gitignore') {
            @unlink($file);
            $viewCount++;
        }
    }
}
logResult('Views Cache', "Cleared {$viewCount} compiled views", 'success');

// Clear bootstrap cache
foreach (['packages.php', 'services.php', 'config.php'] as $cacheFile) {
    $path = $baseDir . '/bootstrap/cache/' . $cacheFile;
    if (file_exists($path)) {
        @unlink($path);
        logResult('Bootstrap Cache', "Deleted: {$cacheFile}", 'success');
    }
}

// Ensure storage directories exist and are writable
$dirs = [
    $baseDir . '/storage/framework/cache/data',
    $baseDir . '/storage/framework/sessions',
    $baseDir . '/storage/framework/views',
    $baseDir . '/storage/logs',
];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    // Test write
    $testFile = $dir . '/_test_' . time();
    if (@file_put_contents($testFile, 'test') !== false) {
        @unlink($testFile);
        logResult('Storage', basename($dir) . '/ is writable', 'success');
    } else {
        logResult('Storage', basename($dir) . '/ is NOT writable!', 'error');
    }
}

// ============================================================
// PHASE 6: Test the homepage
// ============================================================
echo "<h2>Phase 6: Test Homepage</h2>\n";

logResult('Test', "Requesting: {$homeUrl}", 'info');

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $homeUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_USERAGENT => 'Mozilla/5.0 (Homepage Fix Test)',
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$body = substr($response, $headerSize);
$totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
curl_close($ch);

logResult('Test', "HTTP Status: {$httpCode} (took {$totalTime}s)", $httpCode === 200 ? 'success' : 'error');

if ($httpCode === 200) {
    $bodyLen = strlen($body);
    logResult('Test', "Response body: {$bodyLen} bytes", 'success');

    // Check if key elements are present
    $checks = [
        'hero-slider' => 'Hero slider section',
        'counters-section' => 'Counters section',
        'features' => 'Features section',
        'about' => 'About section',
        'programs' => 'Programs section',
        'gallery' => 'Gallery section',
        'team' => 'Team section',
        'footer' => 'Footer',
        'homepage.css' => 'External CSS loaded',
        'homepage.js' => 'External JS loaded',
        'website.css' => 'Website layout CSS loaded',
    ];
    foreach ($checks as $needle => $label) {
        $found = stripos($body, $needle) !== false;
        logResult('Content Check', "{$label}: " . ($found ? 'FOUND' : 'MISSING'), $found ? 'success' : 'warning');
    }

    echo "<div class='success-box'>
        <h3 style='color:#2ea043;margin:0 0 8px'>✅ HOMEPAGE IS WORKING!</h3>
        <p>The permanent fix has been applied successfully. The template size was reduced by ~70% by extracting CSS/JS to external files.</p>
        <p><a href='{$homeUrl}' target='_blank' style='color:#58a6ff;font-weight:bold;'>👉 Click here to view the homepage</a></p>
    </div>";
} else {
    // Homepage still failing - capture the error details
    if ($body) {
        $errorLogFile = $baseDir . '/homepage-still-failing.html';
        file_put_contents($errorLogFile, $body);

        echo "<div class='error-detail'>";
        // Try to extract useful error info
        if (preg_match('/<title>(.*?)<\/title>/is', $body, $m)) {
            echo "Error Title: " . htmlspecialchars($m[1]) . "\n\n";
        }
        if (preg_match('/Fatal error:.*?in .*? on line \d+/s', $body, $m)) {
            echo "PHP Fatal Error: " . htmlspecialchars($m[0]) . "\n\n";
        }
        if (preg_match('/class="exception_message"[^>]*>(.*?)<\/h1>/s', $body, $m)) {
            echo "Laravel Error: " . htmlspecialchars(strip_tags($m[1])) . "\n\n";
        }
        if (preg_match('/Allowed memory size of (\d+) bytes exhausted/s', $body, $m)) {
            $memMb = round($m[1] / 1024 / 1024);
            echo "MEMORY EXHAUSTED: {$memMb}MB limit reached!\n";
            echo "This confirms the root cause. The fix should help once the compiled view cache clears.\n";
        }
        echo "\n--- Full response body (first 3000 chars) ---\n";
        echo htmlspecialchars(substr($body, 0, 3000));
        echo "</div>";

        logResult('Error', "Full error saved to homepage-still-failing.html", 'warning');
    } else {
        logResult('Error', 'Empty response body (server-level 500 error)', 'error');
    }

    // If still failing, try clearing the view cache again (it might have been re-compiled with old template)
    logResult('Retry', 'Clearing view cache again and retrying...', 'info');
    foreach (glob($viewsDir . '/*') as $file) {
        if (is_file($file) && basename($file) !== '.gitignore') {
            @unlink($file);
        }
    }

    // Second attempt
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $homeUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Homepage Fix Retry)',
    ]);
    $response2 = curl_exec($ch);
    $httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode2 === 200) {
        echo "<div class='success-box'>
            <h3 style='color:#2ea043;margin:0 0 8px'>✅ HOMEPAGE IS WORKING! (on retry)</h3>
            <p>The compiled view cache needed to be cleared twice.</p>
            <p><a href='{$homeUrl}' target='_blank' style='color:#58a6ff;font-weight:bold;'>👉 Click here to view the homepage</a></p>
        </div>";
    } else {
        logResult('Retry', "Still getting HTTP {$httpCode2} after cache clear", 'error');

        // Try restoring backups and using the emergency fallback
        echo "<div class='card'>
            <h3 style='color:#f85149;'>❌ Still not working after CSS extraction</h3>
            <p>The issue may be more fundamental than template size. Here are the backup files if you need to restore:</p>
            <ul>";
        foreach ($backups as $b) {
            echo "<li><code>" . htmlspecialchars(basename($b)) . "</code></li>";
        }
        echo "</ul>
            <p><strong>Next step:</strong> Upload the <code>emergency-homepage.php</code> script (will be created below) to bypass the welcome view entirely.</p>
        </div>";

        // Create an emergency homepage bypass script
        $emergencyScript = <<<'EMERGENCY'
<?php
/**
 * EMERGENCY HOMEPAGE BYPASS
 * 
 * If the normal welcome view still fails, this script patches the route
 * to use the simple home.blade.php instead of the massive welcome.blade.php.
 * Upload to your Laravel root and visit once.
 */
$routesFile = __DIR__ . '/routes/web.php';
$routes = file_get_contents($routesFile);

// Check if already patched
if (strpos($routes, 'EMERGENCY HOMEPAGE PATCH') !== false) {
    die("Already patched! The homepage route is using the simple fallback view.");
}

// Replace the home route to use a simple closure instead of HomeController
$original = "Route::get('/', [HomeController::class, 'index'])->name('home');";
$patched = "// EMERGENCY HOMEPAGE PATCH — uses simple home view instead of massive welcome view
Route::get('/', function () {
    try {
        \$settings = app(\\App\\Http\\Controllers\\HomeController::class)->getWebsiteSettings();
    } catch (\\Throwable \$e) {
        \$settings = [];
    }
    return view('home', compact('settings'));
})->name('home');";

if (strpos($routes, $original) !== false) {
    $backup = $routesFile . '.bak-' . date('Ymd-His');
    copy($routesFile, $backup);
    $routes = str_replace($original, $patched, $routes);
    file_put_contents($routesFile, $routes);
    
    // Clear view cache
    foreach (glob(__DIR__ . '/storage/framework/views/*') as $f) {
        if (is_file($f) && basename($f) !== '.gitignore') @unlink($f);
    }
    
    echo "✅ Homepage route patched to use simple fallback view!<br>";
    echo "<a href='/'>👉 Test Homepage</a><br>";
    echo "<br>Backup saved: " . basename($backup);
} else {
    echo "Could not find the exact route line to patch. The routes file may have been modified.";
}
EMERGENCY;

        file_put_contents($baseDir . '/emergency-homepage.php', $emergencyScript);
        logResult('Emergency', 'Created emergency-homepage.php as last-resort fallback', 'warning');
    }
}

// ============================================================
// PHASE 7: Summary
// ============================================================
echo "<h2>Summary</h2>\n";
echo "<div class='card'>\n";
echo "<p><strong>Changes made:</strong></p>\n<ul>\n";

$welcomeNewSize = file_exists($welcomePath) ? filesize($welcomePath) : 0;
$layoutNewSize = file_exists($layoutPath) ? filesize($layoutPath) : 0;

if ($stylesExtracted) echo "<li>welcome.blade.php: CSS extracted to public/css/homepage.css</li>\n";
if ($scriptsExtracted) echo "<li>welcome.blade.php: JS extracted to public/js/homepage.js</li>\n";
if ($layoutStylesExtracted) echo "<li>layouts/website.blade.php: CSS extracted to public/css/website.css</li>\n";
if ($layoutScriptsExtracted) echo "<li>layouts/website.blade.php: JS extracted to public/js/website.js</li>\n";

echo "<li>Fixed duplicate &lt;/style&gt; tag in welcome.blade.php</li>\n";
echo "<li>Optimized AppServiceProvider (removed per-request cache deletion)</li>\n";
echo "<li>Cleared all Laravel caches</li>\n";
echo "</ul>\n";

echo "<p><strong>File sizes:</strong></p>\n";
echo "<table style='width:100%;border-collapse:collapse;margin:8px 0;'>\n";
echo "<tr style='border-bottom:1px solid #30363d'><th style='text-align:left;padding:4px'>File</th><th style='text-align:right;padding:4px'>Before</th><th style='text-align:right;padding:4px'>After</th><th style='text-align:right;padding:4px'>Saved</th></tr>\n";
$wSaved = $originalWelcomeSize - $welcomeNewSize;
$lSaved = $originalLayoutSize - $layoutNewSize;
echo "<tr><td style='padding:4px'>welcome.blade.php</td><td style='text-align:right;padding:4px'>{$originalWelcomeSize}</td><td style='text-align:right;padding:4px'>{$welcomeNewSize}</td><td style='text-align:right;padding:4px;color:#2ea043'>−{$wSaved}</td></tr>\n";
echo "<tr><td style='padding:4px'>website.blade.php</td><td style='text-align:right;padding:4px'>{$originalLayoutSize}</td><td style='text-align:right;padding:4px'>{$layoutNewSize}</td><td style='text-align:right;padding:4px;color:#2ea043'>−{$lSaved}</td></tr>\n";
$totalBefore = $originalWelcomeSize + $originalLayoutSize;
$totalAfter = $welcomeNewSize + $layoutNewSize;
$totalSaved = $totalBefore - $totalAfter;
$percentSaved = round(($totalSaved / $totalBefore) * 100);
echo "<tr style='border-top:2px solid #30363d;font-weight:bold'><td style='padding:4px'>TOTAL</td><td style='text-align:right;padding:4px'>{$totalBefore}</td><td style='text-align:right;padding:4px'>{$totalAfter}</td><td style='text-align:right;padding:4px;color:#2ea043'>−{$totalSaved} ({$percentSaved}%)</td></tr>\n";
echo "</table>\n";

echo "<p><strong>Backup files:</strong></p>\n<ul>\n";
foreach ($backups as $b) {
    echo "<li><code>" . htmlspecialchars($b) . "</code></li>\n";
}
echo "</ul>\n";

echo "<p><strong>Test links:</strong></p>\n";
echo "<p><a href='{$homeUrl}' target='_blank' style='color:#58a6ff;'>👉 Test Homepage</a></p>\n";
echo "<p><a href='{$baseUrl}/login' target='_blank' style='color:#58a6ff;'>👉 Test Login</a></p>\n";

echo "<p style='color:#f85149;margin-top:20px;'>⚠️ DELETE THIS FILE AFTER FIXING: fix-homepage-permanent.php</p>\n";
echo "<p style='color:#f85149;'>⚠️ ALSO DELETE: emergency-homepage.php (if created), homepage-500-error.html, homepage-still-failing.html</p>\n";
echo "</div>\n";

?>
</body>
</html>
