<?php
chdir(__DIR__);
require 'vendor/autoload.php';
 $app = require 'bootstrap/app.php';
 $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Fix Logo on All Documents ===\n\n";

// 1. Check what logo path is stored
echo "Step 1: Checking logo in settings...\n";
 $logoRow = DB::table('settings')->where('key', 'school_logo')->first();
if ($logoRow) {
    echo "  - school_logo value: " . $logoRow->value . "\n";
} else {
    echo "  - school_logo key NOT FOUND in settings table!\n";
    echo "  - Checking all keys with 'logo'...\n";
    $logoKeys = DB::table('settings')->where('key', 'like', '%logo%')->get();
    foreach ($logoKeys as $k) {
        echo "    - key='{$k->key}' value='{$k->value}'\n";
    }
}

// 2. Build the correct logo URL helper snippet
 $logoSnippet = '@php $logoPath = DB::table(\'settings\')->where(\'key\', \'school_logo\')->value(\'value\'); $logoUrl = \'\'; if($logoPath){ if(file_exists(public_path(\'storage/\' . $logoPath))){ $logoUrl = asset(\'storage/\' . $logoPath); } elseif(file_exists(public_path($logoPath))){ $logoUrl = asset($logoPath); } else { $logoUrl = asset($logoPath); } } @endphp';

 $logoImgTag = '@if($logoUrl)<img src="{{ $logoUrl }}" style="height:55px;object-fit:contain;margin-bottom:5px;">@endif';

// 3. Fix foldable certificate
echo "\nStep 2: Fixing foldable certificate...\n";
 $f = 'resources/views/admin/certificate-generate/foldable.blade.php';
 $c = file_get_contents($f);

// Remove the old broken Setting::first() approach
 $c = preg_replace('/@php\s*\$setting\s*=\s*\\\\?App\\\\Models\\\\Setting::first\(\);\s*@endphp/', '', $c);
 $c = preg_replace('/@if\(\$setting\s*&&\s*\$setting->logo_url\)<img[^>]*>\s*@endif/', '', $c);

// Add proper logo snippet after <body>
if (strpos($c, '$logoPath = DB::table') === false) {
    $c = str_replace('<body>', '<body>' . "\n    " . $logoSnippet, $c);
}

// Add logo image before SCHOOL OF REDEMPTION
if (strpos($c, '$logoUrl)') !== false && strpos($c, 'height:55px') === false) {
    $c = str_replace('<h1>SCHOOL OF REDEMPTION</h1>', $logoImgTag . "\n            <h1>SCHOOL OF REDEMPTION</h1>', $c);
}

file_put_contents($f, $c);
echo "  - Foldable certificate fixed!\n";

// 4. Fix regular certificate
echo "\nStep 3: Fixing regular certificate...\n";
 $f2 = 'resources/views/admin/certificate-generate/certificate.blade.php';
 $c2 = file_get_contents($f2);

if (strpos($c2, '$logoPath = DB::table') === false) {
    $c2 = str_replace('<body>', '<body>' . "\n    " . $logoSnippet, $c2);
}
if (strpos($c2, 'height:55px') === false) {
    $c2 = str_replace('<h1>SCHOOL OF REDEMPTION</h1>', $logoImgTag . "\n                <h1>SCHOOL OF REDEMPTION</h1>', $c2);
}

file_put_contents($f2, $c2);
echo "  - Regular certificate fixed!\n";

// 5. Fix ID card print view
echo "\nStep 4: Fixing ID card print view...\n";
 $f3 = 'resources/views/admin/id-card-generate/print.blade.php';
 $c3 = file_get_contents($f3);

if (strpos($c3, '$logoPath = DB::table') === false) {
    $c3 = str_replace('<body>', '<body>' . "\n    " . $logoSnippet, $c3);
}

// Replace the icon in the header with logo
 $oldIcon = '<div class="id-card-header-icon">
            <i class="fas fa-school"></i>
        </div>';
 $newIcon = '@if($logoUrl)
        <div style="width:32px;height:32px;overflow:hidden;border-radius:8px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;">
            <img src="{{ $logoUrl }}" style="width:100%;height:100%;object-fit:contain;padding:3px;">
        </div>
        @else
        <div class="id-card-header-icon">
            <i class="fas fa-school"></i>
        </div>
        @endif';

if (strpos($c3, 'height:55px') === false && strpos($c3, '$logoUrl)') === false) {
    $c3 = str_replace($oldIcon, $newIcon, $c3);
}

file_put_contents($f3, $c3);
echo "  - ID card print view fixed!\n";

// 6. Ensure storage:link
echo "\nStep 5: Checking storage:link...\n";
if (!file_exists('public/storage')) {
    echo shell_exec('php artisan storage:link 2>&1');
    echo "  - Storage link created!\n";
} else {
    echo "  - Storage link already exists.\n";
}

// 7. Verify logo file exists
echo "\nStep 6: Verifying logo file...\n";
if ($logoRow && $logoRow->value) {
    $paths = [
        'storage/' . $logoRow->value,
        'public/storage/' . $logoRow->value,
        $logoRow->value,
        'public/' . $logoRow->value,
    ];
    foreach ($paths as $p) {
        if (file_exists($p)) {
            echo "  - Logo FOUND at: $p\n";
        }
    }
}

echo "\n=== Done! Try generating a certificate or ID card now. ===\n";
