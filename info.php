<?php
/**
 * DIAGNOSTIC: Shows $_SERVER variables for debugging URL routing.
 * DELETE THIS FILE after debugging is complete!
 */
header('Content-Type: text/html; charset=utf-8');
echo '<h2>$_SERVER Variables (for URL routing debug)</h2>';
echo '<table border="1" cellpadding="5" style="border-collapse:collapse;">';
$keys = ['SCRIPT_NAME', 'SCRIPT_FILENAME', 'DOCUMENT_ROOT', 'REQUEST_URI',
         'PHP_SELF', 'HTTP_HOST', 'SERVER_NAME', 'SERVER_PORT', 'HTTPS',
         'REDIRECT_URL', 'REDIRECT_STATUS', 'REDIRECT_SCRIPT_URL'];
foreach ($keys as $k) {
    $v = $_SERVER[$k] ?? '<em>not set</em>';
    echo "<tr><td><b>$k</b></td><td>" . htmlspecialchars($v) . "</td></tr>";
}
echo '</table>';

// Calculate what Laravel would see
echo '<h2>Laravel Base Path Calculation</h2>';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = str_replace('\\', '/', dirname($scriptName));
if ($basePath === '/' || $basePath === '\\') $basePath = '';
echo "<p><b>SCRIPT_NAME:</b> " . htmlspecialchars($scriptName) . "</p>";
echo "<p><b>Calculated Base Path:</b> " . htmlspecialchars($basePath) . "</p>";
echo "<p><b>Expected Base Path:</b> /Redemption</p>";
echo "<p><b>Base Path Correct?</b> " . ($basePath === '/Redemption' || $basePath === '/redemption' ? '<span style="color:green">YES</span>' : '<span style="color:red">NO - THIS IS THE PROBLEM!</span>') . "</p>";

// Check if root index.php exists
echo '<h2>File Checks</h2>';
echo "<p><b>__DIR__:</b> " . __DIR__ . "</p>";
echo "<p><b>Root index.php exists:</b> " . (file_exists(__DIR__ . '/index.php') ? 'YES' : 'NO') . "</p>";
echo "<p><b>public/index.php exists:</b> " . (file_exists(__DIR__ . '/public/index.php') ? 'YES' : 'NO') . "</p>";
echo "<p><b>vendor/autoload.php exists:</b> " . (file_exists(__DIR__ . '/vendor/autoload.php') ? 'YES' : 'NO') . "</p>";
echo "<p><b>.htaccess exists:</b> " . (file_exists(__DIR__ . '/.htaccess') ? 'YES' : 'NO') . "</p>";
echo "<p><b>storage/framework/views exists:</b> " . (is_dir(__DIR__ . '/storage/framework/views') ? 'YES' : 'NO') . "</p>";
echo "<p><b>storage/framework/sessions exists:</b> " . (is_dir(__DIR__ . '/storage/framework/sessions') ? 'YES' : 'NO') . "</p>";
echo "<p><b>bootstrap/cache directory writable:</b> " . (is_writable(__DIR__ . '/bootstrap/cache') ? 'YES' : 'NO') . "</p>";

// Check mod_rewrite
echo '<h2>mod_rewrite Check</h2>';
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    echo "<p><b>mod_rewrite loaded:</b> " . (in_array('mod_rewrite', $modules) ? 'YES' : 'NO') . "</p>";
} else {
    echo "<p><b>apache_get_modules()</b> not available (CGI/FPM mode)</p>";
}

echo '<p style="color:red;"><b>DELETE THIS FILE (info.php) AFTER DEBUGGING!</b></p>';
