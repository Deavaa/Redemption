<?php
chdir(__DIR__);

// 1. Fix foldable certificate
 $f = 'resources/views/admin/certificate-generate/foldable.blade.php';
 $c = file_get_contents($f);

// Remove old broken Setting::first() code
 $c = str_replace('@php $setting = \App\Models\Setting::first(); @endphp', '', $c);
 $c = str_replace('@if($setting && $setting->logo_url)<img src="{{ $setting->logo_url }}" style="height:55px;object-fit:contain;margin-bottom:5px;">@endif', '', $c);

// Add @include for logo partial
if (strpos($c, "partials.logo") === false) {
    $c = str_replace('<body>', '<body>' . "\n    @include('partials.logo')", $c);
    $c = str_replace(
        '<h1>SCHOOL OF REDEMPTION</h1>',
        "@if(\$logoUrl)<img src=\"{{ \$logoUrl }}\" style=\"height:55px;object-fit:contain;margin-bottom:5px;\">@endif\n            <h1>SCHOOL OF REDEMPTION</h1>",
        $c
    );
    file_put_contents($f, $c);
    echo "1. Foldable certificate fixed!\n";
} else {
    echo "1. Already has logo include.\n";
}

// 2. Fix regular certificate
 $f2 = 'resources/views/admin/certificate-generate/certificate.blade.php';
 $c2 = file_get_contents($f2);
if (strpos($c2, "partials.logo") === false) {
    $c2 = str_replace('<body>', '<body>' . "\n    @include('partials.logo')", $c2);
    $c2 = str_replace(
        '<h1>SCHOOL OF REDEMPTION</h1>',
        "@if(\$logoUrl)<img src=\"{{ \$logoUrl }}\" style=\"height:55px;object-fit:contain;margin-bottom:5px;\">@endif\n                <h1>SCHOOL OF REDEMPTION</h1>",
        $c2
    );
    file_put_contents($f2, $c2);
    echo "2. Regular certificate fixed!\n";
} else {
    echo "2. Already has logo include.\n";
}

// 3. Fix ID card print
 $f3 = 'resources/views/admin/id-card-generate/print.blade.php';
 $c3 = file_get_contents($f3);
if (strpos($c3, "partials.logo") === false) {
    $c3 = str_replace('<body>', '<body>' . "\n    @include('partials.logo')", $c3);
    file_put_contents($f3, $c3);
    echo "3. ID card print fixed!\n";
} else {
    echo "3. Already has logo include.\n";
}

echo "\nDone! Now try generating a certificate or ID card.\n";
