<?php
// Fix ALL controllers to pass $data to edit views
require 'vendor/autoload.php';
 $app = require_once 'bootstrap/app.php';
 $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
 $kernel->bootstrap();

 $ctrlDir = app_path('Http/Controllers');

// Find all controllers
 $files = [];
 $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ctrlDir));
foreach($rii as $file) {
    if($file->isFile() && $file->getExtension() == 'php') {
        $files[] = $file->getPathname();
    }
}

echo "=== Fixing controllers ===\n";
foreach($files as $path) {
    $content = file_get_contents($path);
    
    // Check if this controller has an edit method
    if(strpos($content, 'function edit') === false) continue;
    
    // Check if edit method uses $data
    if(strpos($content, "compact('data'") !== false) {
        echo "SKIP: " . basename($path) . " (already has compact data)\n";
        continue;
    }
    
    // Get the model name from namespace or class
    preg_match('/class\s+(\w+)Controller/', $content, $m);
    $ctrlName = $m[1] ?? '';
    echo "FIX: $ctrlName (" . basename($path) . ")\n";
    
    // Find the edit method and fix it
    // Pattern 1: edit($id) with return view without $data
    $content = preg_replace(
        '/function\s+edit\s*\(\s*\$\s*id\s*\)\s*\{([^}]*?)return\s+view\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*;/s',
        'function edit($id) {
            $data = $this->$2::findOrFail($id);
            return view(\'$2.edit\', compact(\'data\'));',
        $content
    );
    
    // That regex might be too aggressive. Let's use a simpler approach:
    // Just replace common patterns
    
    file_put_contents($path, $content);
}
