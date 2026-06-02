<?php

/**
 * ================================================================
 * ARTISAN WEB RUNNER for Shared Hosting
 * ================================================================
 * This script allows you to run Artisan commands via the browser
 * when you don't have SSH access on shared hosting.
 *
 * USAGE: https://yoursite.com/artisan-runner.php?cmd=migrate
 * 
 * SECURITY: DELETE THIS FILE after setup! Or rename it to something
 * only you know.
 * ================================================================
 */

// Only allow specific safe commands
$allowedCommands = [
    'migrate'           => ['migrate', '--force'],
    'migrate:fresh'     => ['migrate:fresh', '--force'],
    'migrate:status'    => ['migrate:status'],
    'migrate:rollback'  => ['migrate:rollback', '--force'],
    'config:cache'      => ['config:cache'],
    'config:clear'      => ['config:clear'],
    'cache:clear'       => ['cache:clear'],
    'route:cache'       => ['route:cache'],
    'route:clear'       => ['route:clear'],
    'view:cache'        => ['view:cache'],
    'view:clear'        => ['view:clear'],
    'storage:link'      => ['storage:link'],
    'key:generate'      => ['key:generate', '--force'],
    'db:seed'           => ['db:seed', '--force'],
    'optimize'          => ['optimize'],
    'optimize:clear'    => ['optimize:clear'],
    'up'                => ['up'],
    'down'              => ['down'],
    'env'               => ['env'],
];

// Simple access protection - change this secret!
$secret = 'CHANGE_THIS_SECRET_12345';

// Check secret
if (!isset($_GET['secret']) || $_GET['secret'] !== $secret) {
    http_response_code(403);
    echo '<h1>403 Forbidden</h1><p>Access denied. Provide ?secret=YOUR_SECRET parameter.</p>';
    exit;
}

// Get command
$cmd = $_GET['cmd'] ?? 'env';

if (!isset($allowedCommands[$cmd])) {
    echo "<h1>Invalid Command</h1>";
    echo "<p>Allowed commands: " . implode(', ', array_keys($allowedCommands)) . "</p>";
    exit;
}

// Run the Artisan command
echo "<h1>Running: php artisan " . implode(' ', $allowedCommands[$cmd]) . "</h1>";
echo "<pre>";

// Change to the Laravel root directory (one level up from public)
chdir(dirname(__DIR__));

// Capture output
$command = escapeshellcmd('php ' . __DIR__ . '/../artisan ' . implode(' ', $allowedCommands[$cmd]));
$output = shell_exec($command . ' 2>&1');

echo htmlspecialchars($output ?? 'No output');
echo "</pre>";

echo "<hr><p><a href='?secret={$secret}&cmd=env'>Back to env</a></p>";
echo "<p><small style='color:red;'>⚠️ DELETE THIS FILE AFTER SETUP for security!</small></p>";
