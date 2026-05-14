<?php
// Fix logo on foldable certificate + certificate duplicate fix

// 1. Foldable certificate - add logo
 $f = 'resources/views/admin/certificate-generate/foldable.blade.php';
 $c = file_get_contents($f);
 $changed = false;

if (strpos($c, 'Setting::first()') === false) {
    $c = str_replace('<body>', '<body>' . "\n" . '    @php $setting = \App\Models\Setting::first(); @endphp', $c);
    $changed = true;
}

if (strpos($c, 'logo_url') === false) {
    $c = str_replace(
        '<h1>SCHOOL OF REDEMPTION</h1>',
        '@if($setting && $setting->logo_url)<img src="{{ $setting->logo_url }}" style="height:55px;object-fit:contain;margin-bottom:5px;">@endif' . "\n        " . '<h1>SCHOOL OF REDEMPTION</h1>',
        $c
    );
    $changed = true;
}

if ($changed) {
    file_put_contents($f, $c);
    echo "1. Foldable certificate: logo added!\n";
} else {
    echo "1. Foldable certificate: already has logo\n";
}

// 2. Certificate controller - fix duplicates
 $ctrl = 'app/Http/Controllers/Certificate/CertificateGenerateController.php';
 $cc = file_get_contents($ctrl);
if (strpos($cc, "where('type', \$r->type)->delete()") === false) {
    $cc = str_replace(
        "// Auto-create certificate record",
        "Certificate::where('student_id', \$student->id)->where('type', \$r->type)->delete();\n\n        // Auto-create certificate record",
        $cc
    );
    file_put_contents($ctrl, $cc);
    echo "2. Certificate controller: duplicate fix added!\n";
} else {
    echo "2. Certificate controller: already fixed\n";
}

// 3. List ID Card view files
echo "\n3. ID Card view files:\n";
foreach (glob('resources/views/admin/IdCard/*.php') as $file) {
    echo "   - " . basename($file) . "\n";
}

// 4. List certificate view files
echo "\n4. Certificate view files:\n";
foreach (glob('resources/views/admin/certificate-generate/*.php') as $file) {
    echo "   - " . basename($file) . "\n";
}

echo "\nDone!\n";
