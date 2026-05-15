<?php
 $base = getcwd();
 $changes = 0;

// ============================================================
// FIX 1: Remove calendar menu item since route doesn't exist
// ============================================================
echo "===== FIX 1: Remove undefined calendar route from sidebar =====\n\n";

 $layoutPath = $base . '/resources/views/layouts/admin.blade.php';
 $layout = file_get_contents($layoutPath);

// Remove the calendar menu item that references undefined route
 $calendarItem = '<li class="{{ request()->routeIs(\'admin.calendar-events.*\') ? \'active\' : \'\' }}">
                    <a href="{{ route(\'admin.calendar-events.index\') }}" class="{{ request()->routeIs(\'admin.calendar-events.*\') ? \'active\' : \'\' }}"><i class="fas fa-calendar-alt"></i><span>Calendar</span></a>
                </li>';

 $layout = str_replace($calendarItem, '', $layout, $c);
if ($c > 0) { echo "OK: Removed calendar menu item\n"; $changes += $c; }
else { echo "Trying alternate format...\n"; }

// Also try with different whitespace
 $layout = preg_replace('/<li class="\{\{ request\(\)->routeIs\(\'admin\.calendar-events\.\*\'\).*?<\/li>\s*/s', '', $layout, $c2);
if ($c2 > 0) { echo "OK: Removed calendar item (regex)\n"; $changes += $c2; }

// Clean up any double blank lines
 $layout = preg_replace('/\n{3,}/', "\n\n", $layout);

file_put_contents($layoutPath, $layout);
echo "Layout saved.\n";

// ============================================================
// FIX 2: Allow one decimal place in mark entry
// ============================================================
echo "\n===== FIX 2: Allow one decimal place in mark entry =====\n\n";

// 2a: Fix MarkEntryController validation
 $ctrlPath = $base . '/app/Http/Controllers/Admin/MarkEntryController.php';
if (file_exists($ctrlPath)) {
    $ctrl = file_get_contents($ctrlPath);
    
    echo "Current validation rules:\n";
    $lines = explode("\n", $ctrl);
    foreach ($lines as $i => $line) {
        if (stripos($line, 'validate') !== false || (stripos($line, 'integer') !== false && stripos($line, '//') === false)) {
            echo "  " . ($i+1) . ": " . trim($line) . "\n";
        }
    }
    
    // Replace integer with numeric for mark fields
    $ctrlReplacements = [
        "'required|integer'" => "'required|numeric'",
        "'required|integer|min:0'" => "'required|numeric|min:0'",
        "'nullable|integer'" => "'nullable|numeric'",
        "'nullable|integer|min:0'" => "'nullable|numeric|min:0'",
    ];
    
    $ctrlChanges = 0;
    foreach ($ctrlReplacements as $old => $new) {
        $ctrl = str_replace($old, $new, $ctrl, $c);
        $ctrlChanges += $c;
    }
    
    if ($ctrlChanges > 0) {
        file_put_contents($ctrlPath, $ctrl);
        echo "OK: Changed integer to numeric in controller ($ctrlChanges replacements)\n";
        $changes += $ctrlChanges;
    } else {
        echo "No integer validation found - may already be numeric\n";
    }
} else {
    echo "Controller not found\n";
}

// 2b: Fix mark entry views - add step="0.1" to number inputs
 $views = [
    $base . '/resources/views/admin/mark-entries/index.blade.php',
    $base . '/resources/views/admin/mark-entries/create.blade.php',
];

foreach ($views as $viewPath) {
    if (!file_exists($viewPath)) continue;
    
    $view = file_get_contents($viewPath);
    $viewChanges = 0;
    
    // Add step="0.1" to number inputs that don't already have it
    // Pattern: type="number" without step
    $view = preg_replace('/type="number"(?![^>]*step=)/i', 'type="number" step="0.1"', $view, -1, $pregCount);
    $viewChanges += $pregCount;
    
    // Also handle: type="number" min="0" (without step)
    // Already handled by above regex
    
    // Handle single-quoted: type='number'
    $view = preg_replace("/type='number'(?![^>]*step=)/i", "type='number' step='0.1'", $view, -1, $pregCount2);
    $viewChanges += $pregCount2;
    
    // Fix JS-created inputs - look for patterns like:
    // .type = 'number'  or  type=\"number\" in template literals
    // These are harder - let's look for specific patterns
    
    // Pattern: max=5 or max=10 etc in JS-generated HTML without step
    if (strpos($view, 'max=') !== false && strpos($view, 'step=') === false) {
        // JS template literals with max but no step
        $view = preg_replace('/max=\\\\?"(\d+)\\\\?"(?![^>]*step=)/i', 'max="$1" step="0.1"', $view, -1, $pregCount3);
        $viewChanges += $pregCount3;
    }
    
    // Remove duplicate step attributes
    $view = preg_replace('/step="0\.1"\s+step="0\.1"/', 'step="0.1"', $view);
    $view = preg_replace("/step='0\.1'\s+step='0\.1'/", "step='0.1'", $view);
    
    if ($viewChanges > 0) {
        file_put_contents($viewPath, $view);
        echo "OK: Updated " . basename($viewPath) . " ($viewChanges changes)\n";
        $changes += $viewChanges;
    } else {
        echo "No changes needed in " . basename($viewPath) . "\n";
    }
}

// 2c: Fix the JS that creates mark input fields dynamically
// The index.blade.php likely generates inputs from JS based on MarkEntry::getMarkFields()
// Let's check and fix
echo "\nChecking for dynamic input creation in JS...\n";
 $indexPath = $base . '/resources/views/admin/mark-entries/index.blade.php';
if (file_exists($indexPath)) {
    $index = file_get_contents($indexPath);
    
    // Look for JS that creates input elements
    if (preg_match_all('/\.type\s*=\s*[\'"]number[\'"]/i', $index, $matches, PREG_OFFSET_CAPTURE)) {
        echo "  Found JS input type=number creation\n";
        
        // Add step after type assignment
        $index = preg_replace(
            '/\.type\s*=\s*[\'"]number[\'"];/i',
            '.type = \'number\'; input.step = \'0.1\';',
            $index, -1, $jsCount
        );
        
        if ($jsCount > 0) {
            // But this might create duplicates if run again
            // Clean up any double step assignments
            $index = preg_replace(
                "/input\.step\s*=\s*'0\.1';\s*input\.step\s*=\s*'0\.1';/i",
                "input.step = '0.1';",
                $index
            );
            
            file_put_contents($indexPath, $index);
            echo "  OK: Added step='0.1' to JS-created inputs ($jsCount)\n";
            $changes += $jsCount;
        }
    }
    
    // Also check for createElement pattern
    if (preg_match('/createElement\s*\(\s*[\'"]input/i', $index)) {
        echo "  Found createElement for input\n";
    }
    
    // Check for HTML template strings with number inputs
    if (preg_match_all('/<input[^>]*type=["\']number["\'][^>]*>/i', $index, $matches)) {
        foreach ($matches[0] as $m) {
            if (strpos($m, 'step') === false) {
                echo "  Found input without step: " . htmlspecialchars(substr($m, 0, 80)) . "...\n";
            }
        }
    }
}

// 2d: Also check create.blade.php for JS input creation
 $createPath = $base . '/resources/views/admin/mark-entries/create.blade.php';
if (file_exists($createPath)) {
    $create = file_get_contents($createPath);
    
    if (preg_match('/\.type\s*=\s*[\'"]number[\'"]/i', $create)) {
        $create = preg_replace(
            '/\.type\s*=\s*[\'"]number[\'"];/i',
            '.type = \'number\'; input.step = \'0.1\';',
            $create, -1, $jsCount
        );
        $create = preg_replace(
            "/input\.step\s*=\s*'0\.1';\s*input\.step\s*=\s*'0\.1';/i",
            "input.step = '0.1';",
            $create
        );
        
        if ($jsCount > 0) {
            file_put_contents($createPath, $create);
            echo "  OK: Added step='0.1' to JS inputs in create.blade.php ($jsCount)\n";
            $changes += $jsCount;
        }
    }
}

echo "\n===== SUMMARY: $changes total changes =====\n";
echo "Run: php artisan view:clear && php artisan cache:clear\n";
