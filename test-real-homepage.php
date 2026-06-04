<?php
/**
 * Real Homepage Test — Makes an ACTUAL HTTP request to the homepage
 * 
 * The previous diagnostic created a synthetic Laravel request object,
 * which can give false positives. This script makes a real HTTP request
 * using curl, just like a browser would.
 * 
 * Upload to: public_html/test-real-homepage.php
 * Access:    https://redemption.byethost4.com/test-real-homepage.php
 * DELETE after fixing!
 */

echo '<html><head><title>Real Homepage Test</title>';
echo '<style>body{font-family:monospace;padding:20px;background:#1a1a2e;color:#e0e0e0}';
echo '.ok{color:#4ade80}.err{color:#f87171}.warn{color:#fbbf24}.info{color:#60a5fa}';
echo 'pre{background:#0d1117;padding:12px;border-radius:8px;overflow-x:auto;border:1px solid #30363d;max-height:400px}';
echo 'h2{color:#c9a84c;border-bottom:1px solid #30363d;padding-bottom:8px}</style></head><body>';

echo '<h1>Real Homepage HTTP Test</h1>';

// Get the site's own URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host;

echo "<p class='info'>Base URL: {$baseUrl}</p>";

// ═══════════════════════════════════════════════════════
// TEST 1: Real HTTP request to homepage
// ═══════════════════════════════════════════════════════
echo '<h2>Test 1: Real HTTP Request to Homepage (/)</h2>';

$homepageUrl = $baseUrl . '/';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $homepageUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Don't follow redirects
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 HomepageDiagnostic/1.0');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "<p class='err'>❌ cURL Error: {$error}</p>";
}

echo "<p>HTTP Status Code: <strong class='" . ($httpCode === 200 ? 'ok' : 'err') . "'>{$httpCode}</strong></p>";

$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

echo '<h3>Response Headers</h3>';
echo '<pre>' . htmlspecialchars($headers) . '</pre>';

if ($httpCode === 500) {
    echo '<h3 class="err">500 Error Response Body</h3>';
    
    // Try to extract the error from the HTML
    if (preg_match('/<h1[^>]*>(.*?)<\/h1>/si', $body, $m)) {
        echo "<p class='err'><strong>Error Title:</strong> " . htmlspecialchars(strip_tags($m[1])) . "</p>";
    }
    if (preg_match('/class="exception_message"(.*?)<\/p>/si', $body, $m)) {
        echo "<p class='err'><strong>Exception:</strong> " . htmlspecialchars(strip_tags($m[1])) . "</p>";
    }
    // Try other error patterns
    if (preg_match('/<span class="exception_message">(.*?)<\/span>/si', $body, $m)) {
        echo "<p class='err'><strong>Exception:</strong> " . htmlspecialchars(strip_tags($m[1])) . "</p>";
    }
    // Look for "Class ... not found"
    if (preg_match('/Class\s+[\'"]?(\S+?)[\'"]?\s+not found/i', $body, $m)) {
        echo "<p class='err'><strong>Missing Class:</strong> {$m[1]}</p>";
    }
    // Look for "Call to undefined method"
    if (preg_match('/Call to undefined method\s+(.*?)\s*\(/i', $body, $m)) {
        echo "<p class='err'><strong>Undefined Method:</strong> {$m[1]}</p>";
    }
    // Look for "BindingResolutionException"
    if (stripos($body, 'BindingResolutionException') !== false) {
        echo "<p class='err'><strong>BindingResolutionException found</strong> — a service can't be resolved</p>";
    }
    // Look for "FatalError" or "Error"
    if (preg_match('/(?:Fatal\s+Error|Error):\s+(.*?)(?:\s+in\s+|\s+Stacktrace|$)/si', $body, $m)) {
        echo "<p class='err'><strong>Fatal Error:</strong> " . htmlspecialchars(strip_tags($m[1])) . "</p>";
    }
    
    echo '<details><summary>Full response body</summary>';
    echo '<pre>' . htmlspecialchars(substr($body, 0, 10000)) . '</pre>';
    echo '</details>';
    
} elseif ($httpCode === 200) {
    echo "<p class='ok'>✅ Homepage returns 200!</p>";
    echo '<details><summary>Response body (first 500 chars)</summary>';
    echo '<pre>' . htmlspecialchars(substr($body, 0, 500)) . '</pre>';
    echo '</details>';
} elseif ($httpCode === 301 || $httpCode === 302 || $httpCode === 307 || $httpCode === 308) {
    echo "<p class='warn'>⚠️ Redirect detected ({$httpCode})</p>";
    // Find the Location header
    if (preg_match('/Location:\s*(.*)/i', $headers, $m)) {
        $redirectUrl = trim($m[1]);
        echo "<p class='warn'>Redirecting to: <code>{$redirectUrl}</code></p>";
        if (strpos($redirectUrl, 'localhost') !== false) {
            echo "<p class='err'>❌ REDIRECTING TO LOCALHOST! This is the redirect-to-localhost bug!</p>";
        }
    }
} else {
    echo "<p class='warn'>Unexpected status code: {$httpCode}</p>";
    echo '<pre>' . htmlspecialchars(substr($body, 0, 3000)) . '</pre>';
}

// ═══════════════════════════════════════════════════════
// TEST 2: Real HTTP request to /login (should work)
// ═══════════════════════════════════════════════════════
echo '<h2>Test 2: Real HTTP Request to /login</h2>';

$loginUrl = $baseUrl . '/login';

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $loginUrl);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HEADER, false);
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch2, CURLOPT_TIMEOUT, 30);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch2, CURLOPT_USERAGENT, 'Mozilla/5.0 HomepageDiagnostic/1.0');

$loginBody = curl_exec($ch2);
$loginCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "<p>Login status: <strong class='" . ($loginCode === 200 ? 'ok' : 'err') . "'>{$loginCode}</strong></p>";

// ═══════════════════════════════════════════════════════
// TEST 3: Check if HomeController.php has been updated
// ═══════════════════════════════════════════════════════
echo '<h2>Test 3: Check HomeController Version</h2>';

$hcPath = __DIR__ . '/app/Http/Controllers/HomeController.php';
if (file_exists($hcPath)) {
    $hcContent = file_get_contents($hcPath);
    if (strpos($hcContent, 'renderHomepage') !== false) {
        echo "<p class='ok'>✅ HomeController has the new fallback code</p>";
    } else {
        echo "<p class='err'>❌ HomeController is the OLD version — needs update!</p>";
        echo "<p class='warn'>Upload the latest HomeController.php from GitHub to cPanel</p>";
    }
    echo "<p class='info'>File size: " . filesize($hcPath) . " bytes</p>";
} else {
    echo "<p class='err'>❌ HomeController.php NOT FOUND!</p>";
}

// ═══════════════════════════════════════════════════════
// TEST 4: Check AppServiceProvider detectAppUrl status
// ═══════════════════════════════════════════════════════
echo '<h2>Test 4: AppServiceProvider Status</h2>';

$aspPath = __DIR__ . '/app/Providers/AppServiceProvider.php';
if (file_exists($aspPath)) {
    $aspContent = file_get_contents($aspPath);
    if (strpos($aspContent, '//$this->detectAppUrl') !== false || strpos($aspContent, '// $this->detectAppUrl') !== false) {
        echo "<p class='warn'>⚠️ detectAppUrl() is COMMENTED OUT again</p>";
        echo "<p class='info'>This may have been re-commented by a git pull or overwrite</p>";
    } elseif (strpos($aspContent, '$this->detectAppUrl()') !== false) {
        echo "<p class='ok'>✅ detectAppUrl() is ACTIVE</p>";
    }
} else {
    echo "<p class='err'>❌ AppServiceProvider.php NOT FOUND!</p>";
}

// ═══════════════════════════════════════════════════════
// TEST 5: Check if there's a conflicting index.html or index.htm
// ═══════════════════════════════════════════════════════
echo '<h2>Test 5: Check for Conflicting Index Files</h2>';

$conflictingFiles = ['index.html', 'index.htm', 'default.html', 'default.htm', 'home.html', 'home.htm'];
foreach ($conflictingFiles as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        echo "<p class='warn'>⚠️ Found: {$file} (" . filesize($path) . " bytes) — may conflict with index.php</p>";
    }
}
echo "<p class='info'>Done checking for conflicting index files</p>";

// ═══════════════════════════════════════════════════════
// TEST 6: Check ByetHost injection
// ═══════════════════════════════════════════════════════
echo '<h2>Test 6: Check for ByetHost Ad/Script Injection</h2>';

if ($httpCode === 200 && !empty($body)) {
    // Check for injected scripts
    if (preg_match('/<script[^>]*src=["\']https?:\/\/(?!cdn\.(jsdelivr|cloudflare)|fonts\.googleapis|code\.jquery)/i', $body, $m)) {
        echo "<p class='warn'>⚠️ Possible injected script found: " . htmlspecialchars($m[0]) . "</p>";
    } else {
        echo "<p class='ok'>✅ No obvious script injection detected</p>";
    }
    
    // Check for iframes
    if (preg_match('/<iframe/i', $body)) {
        echo "<p class='warn'>⚠️ Iframe found in response — may be ad injection</p>";
    }
} elseif ($httpCode === 500) {
    // Check the 500 error body for injection
    if (preg_match('/<iframe/i', $body)) {
        echo "<p class='warn'>⚠️ Iframe found in 500 error page — ByetHost ad injection may be interfering</p>";
    }
    
    // Check if the error is actually from ByetHost, not Laravel
    if (preg_match('/byethost|byet\.host/i', $body)) {
        echo "<p class='warn'>⚠️ Error page contains ByetHost branding — this might be a ByetHost error, not Laravel</p>";
    }
    
    // Check for PHP fatal error (not Laravel error)
    if (preg_match('/Fatal error:/i', $body)) {
        echo "<p class='err'>❌ PHP Fatal Error detected — this is a PHP-level error, not a Laravel exception</p>";
    }
}

// ═══════════════════════════════════════════════════════
// TEST 7: PHP configuration
// ═══════════════════════════════════════════════════════
echo '<h2>Test 7: PHP Configuration</h2>';
echo "<p class='info'>PHP Version: " . PHP_VERSION . "</p>";
echo "<p class='info'>Memory Limit: " . ini_get('memory_limit') . "</p>";
echo "<p class='info'>Max Execution Time: " . ini_get('max_execution_time') . "</p>";
echo "<p class='info'>Display Errors: " . ini_get('display_errors') . "</p>";
echo "<p class='info'>Error Reporting: " . ini_get('error_reporting') . "</p>";

// ═══════════════════════════════════════════════════════
// TEST 8: Check Laravel error log AFTER the request
// ═══════════════════════════════════════════════════════
echo '<h2>Test 8: Laravel Error Log (After Request)</h2>';

$logDir = __DIR__ . '/storage/logs';
$logFiles = glob($logDir . '/laravel*.log');
if ($logFiles) {
    usort($logFiles, function($a, $b) { return filemtime($b) - filemtime($a); });
    $latestLog = $logFiles[0];
    $logContent = file_get_contents($latestLog);
    
    // Get only the last entry (most recent error)
    $lastEntryStart = strrpos($logContent, '[');
    if ($lastEntryStart !== false) {
        $lastEntry = substr($logContent, $lastEntryStart);
        // Limit to reasonable size
        if (strlen($lastEntry) > 5000) {
            $lastEntry = substr($lastEntry, 0, 5000);
        }
        echo '<details><summary>Latest log entry</summary>';
        echo '<pre>' . htmlspecialchars($lastEntry) . '</pre>';
        echo '</details>';
    }
} else {
    echo '<p class="info">No Laravel log files found</p>';
}

echo '<hr>';
echo '<p><a href="/">Visit Homepage</a> | <a href="/login">Visit Login</a></p>';
echo '<p><strong style="color:#f87171">DELETE THIS FILE: test-real-homepage.php</strong></p>';
echo '</body></html>';
