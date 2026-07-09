<?php
/**
 * IMAGE UPLOAD DIAGNOSTIC for cPanel
 * ----------------------------------------------------------------------------
 * Upload this to your public/ folder and visit:
 * https://yourdomain.com/diag-images.php
 *
 * This checks everything that could cause image upload failures:
 * - PHP upload limits (upload_max_filesize, post_max_size, memory_limit)
 * - GD extension availability
 * - Storage directory permissions
 * - Temporary directory writability
 * - .env file configuration
 * - Actual file upload test
 *
 * ⚠️ DELETE THIS FILE after use.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<!DOCTYPE html><html><head><title>Image Upload Diagnostics</title>';
echo '<style>
body{font-family:monospace;padding:20px;max-width:900px;margin:0 auto;background:#f8fafc;color:#0f172a;}
h1{color:#047857;}h2{color:#1e40af;margin-top:24px;border-bottom:2px solid #e2e8f0;padding-bottom:6px;}
.ok{color:#059669;font-weight:bold;}.err{color:#dc2626;font-weight:bold;}.warn{color:#d97706;font-weight:bold;}
table{border-collapse:collapse;width:100%;margin:10px 0;}
td,th{border:1px solid #e2e8f0;padding:8px 12px;text-align:left;font-size:13px;}
th{background:#f1f5f9;}
.code{background:#1e293b;color:#e2e8f0;padding:12px;border-radius:8px;font-size:12px;overflow-x:auto;margin:10px 0;}
</style></head><body>';

echo '<h1>📷 Image Upload Diagnostics</h1>';
echo '<p>This checks everything that could cause image upload failures on cPanel.</p>';

// ═══════════════════════════════════════════════════════════════════════
// 1. PHP UPLOAD LIMITS
// ═══════════════════════════════════════════════════════════════════════
echo '<h2>1. PHP Upload Limits</h2>';
echo '<table><tr><th>Setting</th><th>Current Value</th><th>Required</th><th>Status</th></tr>';

$limits = [
    ['upload_max_filesize', '60M', 'Must be ≥ 60M for large phone photos'],
    ['post_max_size', '65M', 'Must be ≥ 65M (slightly larger than upload_max_filesize)'],
    ['memory_limit', '256M', 'Must be ≥ 256M for image processing'],
    ['max_execution_time', '300', 'Must be ≥ 300 seconds for large image compression'],
    ['max_input_time', '120', 'Must be ≥ 120 seconds for slow uploads'],
];

foreach ($limits as $limit) {
    $key = $limit[0];
    $current = ini_get($key);
    $required = $limit[1];
    $desc = $limit[2];

    // Convert to bytes for comparison
    $currentBytes = returnBytes($current);
    $requiredBytes = returnBytes($required);
    $ok = $currentBytes >= $requiredBytes;

    echo '<tr>';
    echo '<td><strong>' . $key . '</strong></td>';
    echo '<td>' . $current . '</td>';
    echo '<td>' . $required . '</td>';
    echo '<td class="' . ($ok ? 'ok' : 'err') . '">' . ($ok ? '✓ OK' : '✗ TOO LOW') . '</td>';
    echo '</tr>';
}
echo '</table>';

echo '<p><strong>If any limits are too low, fix them by:</strong></p>';
echo '<div class="code">';
echo 'Option A: cPanel → MultiPHP Manager → edit the .user.ini file<br>';
echo 'Add these lines:<br>';
echo 'upload_max_filesize = 60M<br>';
echo 'post_max_size = 65M<br>';
echo 'memory_limit = 256M<br>';
echo 'max_execution_time = 300<br>';
echo '<br>';
echo 'Option B: Create/edit a file named ".user.ini" in your public/ folder<br>';
echo 'with the same lines above.';
echo '</div>';

// ═══════════════════════════════════════════════════════════════════════
// 2. GD EXTENSION
// ═══════════════════════════════════════════════════════════════════════
echo '<h2>2. GD Extension (Image Processing)</h2>';
if (extension_loaded('gd')) {
    $gdInfo = gd_info();
    echo '<p class="ok">✓ GD extension is loaded.</p>';
    echo '<table><tr><th>Feature</th><th>Status</th></tr>';
    foreach (['JPEG Support' => 'JPEG', 'PNG Support' => 'PNG', 'GIF Read Support' => 'GIF', 'WebP Support' => 'WebP'] as $key => $label) {
        $enabled = isset($gdInfo[$key]) ? $gdInfo[$key] : false;
        echo '<tr><td>' . $label . '</td><td class="' . ($enabled ? 'ok' : 'err') . '">' . ($enabled ? '✓ Enabled' : '✗ Disabled') . '</td></tr>';
    }
    echo '</table>';
} else {
    echo '<p class="err">✗ GD extension is NOT loaded! Image compression will fail.</p>';
    echo '<div class="code">';
    echo 'FIX: cPanel → Software → MultiPHP Manager → PHP Extensions<br>';
    '→ Search for "gd" → Check the box → Apply<br>';
    '<br>';
    'If GD is not available, the system will fall back to storing<br>';
    'the original image without compression (images will still upload).';
    echo '</div>';
}

// ═══════════════════════════════════════════════════════════════════════
// 3. STORAGE DIRECTORY PERMISSIONS
// ═══════════════════════════════════════════════════════════════════════
echo '<h2>3. Storage Directory Permissions</h2>';

// Find the Laravel root (one level up from public/)
$laravelRoot = dirname(__DIR__);
$publicRoot = __DIR__;

$dirs = [
    'storage/app' => $laravelRoot . '/storage/app',
    'storage/app/public' => $laravelRoot . '/storage/app/public',
    'storage/app/public/team-photos' => $laravelRoot . '/storage/app/public/team-photos',
    'storage/app/public/news-images' => $laravelRoot . '/storage/app/public/news-images',
    'storage/app/public/gallery' => $laravelRoot . '/storage/app/public/gallery',
    'storage/framework' => $laravelRoot . '/storage/framework',
    'storage/framework/views' => $laravelRoot . '/storage/framework/views',
    'storage/framework/sessions' => $laravelRoot . '/storage/framework/sessions',
    'storage/framework/cache' => $laravelRoot . '/storage/framework/cache',
    'storage/logs' => $laravelRoot . '/storage/logs',
    'bootstrap/cache' => $laravelRoot . '/bootstrap/cache',
    'public/team-photos (fallback)' => $publicRoot . '/team-photos',
    'public/news-images (fallback)' => $publicRoot . '/news-images',
    'public/gallery (fallback)' => $publicRoot . '/gallery',
    'public/storage (symlink)' => $publicRoot . '/storage',
];

echo '<table><tr><th>Directory</th><th>Exists</th><th>Writable</th><th>Permissions</th><th>Status</th></tr>';

foreach ($dirs as $label => $path) {
    $exists = file_exists($path);
    $writable = is_writable($path);
    $perms = $exists ? substr(sprintf('%o', fileperms($path)), -4) : '—';
    $isSymlink = $exists && is_link($path);

    echo '<tr>';
    echo '<td>' . $label . ($isSymlink ? ' <em>(symlink)</em>' : '') . '</td>';
    echo '<td>' . ($exists ? '✓' : '✗') . '</td>';
    echo '<td>' . ($writable ? '<span class="ok">✓ Yes</span>' : '<span class="err">✗ No</span>') . '</td>';
    echo '<td>' . $perms . '</td>';

    if (!$exists) {
        // Try to create it
        $status = '✗ Missing';
        if (!is_link($path)) {
            @mkdir($path, 0775, true);
            if (file_exists($path)) {
                $status = '<span class="ok">✓ Created now</span>';
            } else {
                $status = '<span class="err">✗ Could not create</span>';
            }
        }
        echo '<td>' . $status . '</td>';
    } elseif ($writable) {
        echo '<td class="ok">✓ OK</td>';
    } else {
        // Try to fix permissions
        @chmod($path, 0775);
        if (is_writable($path)) {
            echo '<td class="ok">✓ Fixed (chmod 775)</td>';
        } else {
            echo '<td class="err">✗ Needs chmod 775</td>';
        }
    }
    echo '</tr>';
}
echo '</table>';

echo '<p><strong>If directories are not writable, fix with:</strong></p>';
echo '<div class="code">';
echo 'In cPanel File Manager, right-click each folder → Permissions → set to 755 or 775<br>';
echo '<br>';
echo 'Or add this to your .htaccess file in public/:<br>';
echo '&lt;IfModule mod_suphp.c&gt;<br>';
echo '  suPHP_ConfigPath /home/yourusername/public_html<br>';
echo '&lt;/IfModule&gt;';
echo '</div>';

// ═══════════════════════════════════════════════════════════════════════
// 4. PHP TEMP DIRECTORY
// ═══════════════════════════════════════════════════════════════════════
echo '<h2>4. PHP Temporary Directory</h2>';
$tempDir = sys_get_temp_dir();
$tempWritable = is_writable($tempDir);
echo '<table><tr><th>Setting</th><th>Value</th><th>Writable</th></tr>';
echo '<tr><td>upload_tmp_dir</td><td>' . (ini_get('upload_tmp_dir') ?: '(system default: ' . $tempDir . ')') . '</td>';
echo '<td class="' . ($tempWritable ? 'ok' : 'err') . '">' . ($tempWritable ? '✓ Yes' : '✗ No') . '</td></tr>';
echo '</table>';

if (!$tempWritable) {
    echo '<p class="err">✗ PHP temp directory is not writable! Uploads will fail.</p>';
    echo '<div class="code">FIX: Contact your hosting provider, OR add to .user.ini:<br>upload_tmp_dir = ' . $laravelRoot . '/storage/tmp<br>Then create that folder and chmod 777.</div>';
}

// ═══════════════════════════════════════════════════════════════════════
// 5. TEST FILE UPLOAD
// ═══════════════════════════════════════════════════════════════════════
echo '<h2>5. File Upload Test</h2>';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    $file = $_FILES['test_file'];
    echo '<table><tr><th>Property</th><th>Value</th></tr>';
    echo '<tr><td>Original Name</td><td>' . htmlspecialchars($file['name']) . '</td></tr>';
    echo '<tr><td>Type</td><td>' . $file['type'] . '</td></tr>';
    echo '<tr><td>Size</td><td>' . round($file['size'] / 1024, 1) . ' KB</td></tr>';
    echo '<tr><td>Temp Path</td><td>' . $file['tmp_name'] . '</td></tr>';
    echo '<tr><td>Error Code</td><td>' . $file['error'] . '</td></tr>';
    echo '<tr><td>Error Message</td><td>';

    $uploadErrors = [
        UPLOAD_ERR_OK => 'No error — upload successful',
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize (' . ini_get('upload_max_filesize') . ')',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload',
    ];
    $errorMsg = $uploadErrors[$file['error']] ?? 'Unknown error';
    echo $file['error'] === UPLOAD_ERR_OK ? '<span class="ok">' . $errorMsg . '</span>' : '<span class="err">' . $errorMsg . '</span>';
    echo '</td></tr>';

    // Try to move the file
    if ($file['error'] === UPLOAD_ERR_OK && $file['tmp_name']) {
        $testDest = $laravelRoot . '/storage/app/public/upload_test_' . time() . '.txt';
        if (@move_uploaded_file($file['tmp_name'], $testDest)) {
            echo '<tr><td>Move Test</td><td class="ok">✓ File saved to storage/app/public/ successfully</td></tr>';
            @unlink($testDest); // Clean up
        } else {
            echo '<tr><td>Move Test</td><td class="err">✗ Could not save file to storage/app/public/ — permission issue</td></tr>';
        }
    }
    echo '</table>';
    echo '<p><a href="?">← Run test again</a></p>';
} else {
    echo '<form method="POST" enctype="multipart/form-data" style="background:#fff;padding:16px;border-radius:10px;border:1px solid #e2e8f0;">';
    echo '<p>Select any image file to test the upload mechanism:</p>';
    echo '<input type="file" name="test_file" accept="image/*" style="margin:8px 0;">';
    echo '<button type="submit" style="padding:10px 20px;background:#047857;color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600;">Test Upload</button>';
    echo '</form>';
}

// ═══════════════════════════════════════════════════════════════════════
// 6. .ENV FILE CHECK
// ═══════════════════════════════════════════════════════════════════════
echo '<h2>6. .env File Check</h2>';
$envPath = $laravelRoot . '/.env';
if (file_exists($envPath)) {
    echo '<p class="ok">✓ .env file exists.</p>';
    $envContent = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $importantKeys = ['APP_URL', 'APP_KEY', 'DB_DATABASE', 'DB_USERNAME', 'SESSION_DRIVER', 'FILESYSTEM_DISK'];
    echo '<table><tr><th>Key</th><th>Value</th><th>Status</th></tr>';
    foreach ($importantKeys as $key) {
        $found = false;
        foreach ($envContent as $line) {
            if (strpos($line, $key . '=') === 0) {
                $value = substr($line, strlen($key) + 1);
                $value = trim($value, '"\'');
                $displayValue = in_array($key, ['DB_PASSWORD', 'APP_KEY']) ? '•••••• (hidden)' : $value;
                $status = empty($value) ? '<span class="err">✗ Empty</span>' : '<span class="ok">✓ Set</span>';
                echo '<tr><td>' . $key . '</td><td>' . $displayValue . '</td><td>' . $status . '</td></tr>';
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo '<tr><td>' . $key . '</td><td>—</td><td class="warn">⚠ Not set</td></tr>';
        }
    }
    echo '</table>';
} else {
    echo '<p class="err">✗ .env file NOT FOUND at ' . $envPath . '</p>';
    echo '<p>Create it by copying .env.example and setting your database credentials.</p>';
}

// ═══════════════════════════════════════════════════════════════════════
// 7. SUMMARY
// ═══════════════════════════════════════════════════════════════════════
echo '<h2>📋 Summary & Action Items</h2>';
echo '<div class="code">';
echo "Laravel Root: {$laravelRoot}\n";
echo "Public Root: {$publicRoot}\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "GD Loaded: " . (extension_loaded('gd') ? 'YES' : 'NO') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "Temp dir: " . $tempDir . " (" . ($tempWritable ? 'writable' : 'NOT writable') . ")\n";
echo '</div>';

echo '<div style="background:#fef3c7;border:1px solid #fde68a;border-radius:10px;padding:16px;margin-top:20px;">';
echo '<strong>⚠️ SECURITY:</strong> Delete this file (diag-images.php) from your server NOW!';
echo '</div>';

echo '</body></html>';

function returnBytes($val) {
    $val = trim($val);
    if (empty($val)) return 0;
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}
