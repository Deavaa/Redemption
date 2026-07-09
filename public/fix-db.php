<?php
/**
 * STANDALONE DATABASE FIX SCRIPT
 * ----------------------------------------------------------------------------
 * Upload this file directly to your cPanel public_html/ (or wherever your
 * domain points). Then visit https://schoolofredemption.net/fix-db.php
 *
 * This script:
 * 1. Connects to the database using Laravel's .env file
 * 2. Finds ALL tables missing AUTO_INCREMENT on their 'id' column
 * 3. Fixes each one by adding PRIMARY KEY + AUTO_INCREMENT
 * 4. Prints the results
 *
 * ⚠️ DELETE THIS FILE after use — it's a security risk if left accessible.
 */

// ── Load .env file ──────────────────────────────────────────────────────
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    // Try one level up (if this file is in public/)
    $envPath = dirname(__DIR__) . '/.env';
}
if (!file_exists($envPath)) {
    die('<h1>ERROR: .env file not found</h1><p>Could not find .env file. This script must be placed in the Laravel project root or public/ folder.</p>');
}

$env = [];
foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') === false) continue;
    list($key, $value) = explode('=', $line, 2);
    $env[trim($key)] = trim($value, '"\'');
}

$dbHost = $env['DB_HOST'] ?? '127.0.0.1';
$dbPort = $env['DB_PORT'] ?? '3306';
$dbName = $env['DB_DATABASE'] ?? '';
$dbUser = $env['DB_USERNAME'] ?? '';
$dbPass = $env['DB_PASSWORD'] ?? '';

if (empty($dbName) || empty($dbUser)) {
    die('<h1>ERROR: Database credentials not found in .env</h1><p>DB_DATABASE and DB_USERNAME must be set.</p>');
}

// ── Connect to MySQL ────────────────────────────────────────────────────
try {
    $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 300,
    ]);
} catch (PDOException $e) {
    die('<h1>Database connection failed</h1><p>' . htmlspecialchars($e->getMessage()) . '</p><p>Check DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD in your .env file.</p>');
}

echo '<!DOCTYPE html><html><head><title>Fix Database</title><style>body{font-family:monospace;padding:20px;max-width:900px;margin:0 auto;background:#f8fafc;color:#0f172a;}h1{color:#047857;}pre{background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:16px;overflow-x:auto;}.ok{color:#059669;}.err{color:#dc2626;}.warn{color:#d97706;}</style></head><body>';
echo '<h1>🔧 Database Fix Script</h1>';
echo '<p><strong>Database:</strong> ' . htmlspecialchars($dbName) . '</p>';
echo '<pre>';

// ── Step 1: Create migrations table if missing ──────────────────────────
echo "=== STEP 1: Ensure migrations table exists ===\n";
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `migrations` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `migration` varchar(255) NOT NULL,
        `batch` int NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<span class='ok'>✓ migrations table ready.</span>\n\n";
} catch (PDOException $e) {
    echo "<span class='err'>✗ Could not create migrations table: " . htmlspecialchars($e->getMessage()) . "</span>\n\n";
}

// ── Step 2: Find ALL tables missing AUTO_INCREMENT on 'id' ──────────────
echo "=== STEP 2: Scanning all tables for missing AUTO_INCREMENT ===\n";
$stmt = $pdo->prepare("
    SELECT TABLE_NAME, COLUMN_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = ?
      AND COLUMN_NAME = 'id'
      AND EXTRA NOT LIKE '%auto_increment%'
    ORDER BY TABLE_NAME
");
$stmt->execute([$dbName]);
$brokenTables = $stmt->fetchAll();

if (empty($brokenTables)) {
    echo "<span class='ok'>✓ All tables already have AUTO_INCREMENT on 'id'. Nothing to fix!</span>\n\n";
} else {
    echo "Found " . count($brokenTables) . " table(s) needing fix:\n";
    foreach ($brokenTables as $t) {
        echo "  - " . $t['TABLE_NAME'] . "\n";
    }
    echo "\n";
}

// ── Step 3: Fix each table ──────────────────────────────────────────────
echo "=== STEP 3: Fixing tables ===\n";
$fixed = 0;
$failed = 0;

foreach ($brokenTables as $table) {
    $tableName = $table['TABLE_NAME'];
    $columnType = $table['COLUMN_TYPE'];
    $isBigint = strpos($columnType, 'bigint') !== false;
    $typeSql = $isBigint ? 'BIGINT UNSIGNED' : 'INT UNSIGNED';

    echo "Fixing `{$tableName}`... ";

    try {
        // Step 3a: Drop existing primary key (if any)
        try {
            $pdo->exec("ALTER TABLE `{$tableName}` DROP PRIMARY KEY");
            echo "dropped old PK, ";
        } catch (PDOException $e) {
            // No primary key to drop — that's OK
            echo "no PK to drop, ";
        }

        // Step 3b: Modify id column to be AUTO_INCREMENT PRIMARY KEY
        $pdo->exec("ALTER TABLE `{$tableName}` MODIFY COLUMN `id` {$typeSql} NOT NULL AUTO_INCREMENT PRIMARY KEY");
        echo "<span class='ok'>✓ FIXED</span>\n";
        $fixed++;
    } catch (PDOException $e) {
        echo "<span class='err'>✗ FAILED: " . htmlspecialchars($e->getMessage()) . "</span>\n";
        $failed++;
    }
}

echo "\n";
echo "=== RESULTS ===\n";
echo "<span class='ok'>Fixed: {$fixed} table(s)</span>\n";
if ($failed > 0) {
    echo "<span class='err'>Failed: {$failed} table(s)</span>\n";
}
echo "\n";

// ── Step 4: Verify ──────────────────────────────────────────────────────
echo "=== STEP 4: Verification — re-scan ===\n";
$stmt->execute([$dbName]);
$stillBroken = $stmt->fetchAll();
if (empty($stillBroken)) {
    echo "<span class='ok'>✓ All tables now have AUTO_INCREMENT. Database is fixed!</span>\n\n";
} else {
    echo "<span class='err'>Still broken: " . count($stillBroken) . " table(s):</span>\n";
    foreach ($stillBroken as $t) {
        echo "  - " . $t['TABLE_NAME'] . "\n";
    }
    echo "\n";
}

// ── Step 5: Also add deleted_at to users if missing ─────────────────────
echo "=== STEP 5: Check users table for deleted_at column ===\n";
try {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `users` LIKE 'deleted_at'");
    $stmt->execute();
    if ($stmt->fetch() === false) {
        echo "Adding deleted_at column to users... ";
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL");
        $pdo->exec("ALTER TABLE `users` ADD INDEX `users_deleted_at_index` (`deleted_at`)");
        echo "<span class='ok'>✓ Added.</span>\n";
    } else {
        echo "<span class='ok'>✓ deleted_at already exists.</span>\n";
    }
} catch (PDOException $e) {
    echo "<span class='warn'>⚠ " . htmlspecialchars($e->getMessage()) . "</span>\n";
}

echo "\n=== STEP 6: Check users table for must_change_password column ===\n";
try {
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `users` LIKE 'must_change_password'");
    $stmt->execute();
    if ($stmt->fetch() === false) {
        echo "Adding must_change_password column to users... ";
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `must_change_password` TINYINT(1) NOT NULL DEFAULT 0");
        echo "<span class='ok'>✓ Added.</span>\n";
    } else {
        echo "<span class='ok'>✓ must_change_password already exists.</span>\n";
    }
} catch (PDOException $e) {
    echo "<span class='warn'>⚠ " . htmlspecialchars($e->getMessage()) . "</span>\n";
}

echo "\n";
echo "=== DONE ===\n";
echo "</pre>";
echo '<div style="background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:16px;margin-top:20px;">';
echo '<strong>⚠️ SECURITY WARNING:</strong> Delete this file (<code>fix-db.php</code>) from your server NOW!';
echo ' Leaving it accessible is a security risk.';
echo '</div>';
echo '</body></html>';
