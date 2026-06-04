<?php

/**
 * ──────────────────────────────────────────────────────────────
 * DATABASE MIGRATION RUNNER for Shared Hosting
 * ──────────────────────────────────────────────────────────────
 * Usage: https://yoursite.com/migrate.php?secret=CHANGE_THIS_SECRET_12345&cmd=migrate
 *
 * DELETE THIS FILE AFTER RUNNING MIGRATIONS!
 * ──────────────────────────────────────────────────────────────
 */

$secret = 'CHANGE_THIS_SECRET_12345';

// Check secret
if (!isset($_GET['secret']) || $_GET['secret'] !== $secret) {
    http_response_code(403);
    echo '<h1>403 Forbidden</h1><p>Access denied. Provide ?secret=YOUR_SECRET parameter.</p>';
    exit;
}

// Change to Laravel root directory
chdir(__DIR__);

// Bootstrap Laravel minimally
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$allowedCommands = [
    'migrate'           => ['migrate', '--force'],
    'migrate:fresh'     => ['migrate:fresh', '--force'],
    'migrate:status'    => ['migrate:status'],
    'migrate:rollback'  => ['migrate:rollback', '--force'],
    'config:cache'      => ['config:cache'],
    'config:clear'      => ['config:clear'],
    'cache:clear'       => ['cache:clear'],
    'route:clear'       => ['route:clear'],
    'view:clear'        => ['view:clear'],
    'key:generate'      => ['key:generate', '--force'],
    'optimize:clear'    => ['optimize:clear'],
    'db:seed'           => ['db:seed', '--force'],
    'env'               => ['env'],
];

$cmd = $_GET['cmd'] ?? 'migrate:status';

if (!isset($allowedCommands[$cmd])) {
    echo "<h1>Invalid Command</h1>";
    echo "<p>Allowed commands: " . implode(', ', array_keys($allowedCommands)) . "</p>";
    exit;
}

$args = $allowedCommands[$cmd];

echo "<h1>Running: php artisan " . implode(' ', $args) . "</h1>";
echo "<pre>";

try {
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $status = $kernel->call(implode(' ', $args));
    $output = $kernel->output();
    echo htmlspecialchars($output);
} catch (\Throwable $e) {
    echo "ERROR: " . htmlspecialchars($e->getMessage()) . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . htmlspecialchars($e->getTraceAsString());
}

echo "</pre>";

echo "<hr>";
echo "<h2>Quick Links:</h2>";
echo "<ul>";
foreach (['migrate:status', 'migrate', 'config:clear', 'cache:clear', 'optimize:clear'] as $c) {
    echo "<li><a href='?secret={$secret}&cmd={$c}'>{$c}</a></li>";
}
echo "</ul>";
echo "<p style='color:red;'><strong>DELETE THIS FILE AFTER SETUP for security!</strong></p>";
