<?php
 $b = dirname(__DIR__);
 $d = "";
for ($i = 1; file_exists(__DIR__ . "/c{$i}.b64"); $i++) {
    $d .= trim(file_get_contents(__DIR__ . "/c{$i}.b64"));
}
if (!$d) { echo "No chunks found\n"; exit(1); }
 $gz = base64_decode($d);
echo "Decoded " . strlen($gz) . " bytes\n";
if (strlen($gz) < 14000) { echo "Data too small, probably truncated!\n"; exit(1); }
file_put_contents(__DIR__ . "/mr.tar.gz", $gz);
try {
    $p = new PharData(__DIR__ . "/mr.tar.gz");
    $p->extractTo($b);
    echo "PharData SUCCESS!\n";
} catch (Exception $e) {
    echo "PharData failed, trying exec...\n";
    $cmd = "tar xzf \"" . __DIR__ . "\\mr.tar.gz\" -C \"" . $b . "\"";
    passthru($cmd, $rc);
    if ($rc !== 0) { echo "FAILED\n"; exit(1); }
}
unlink(__DIR__ . "/mr.tar.gz");
for ($i = 1; file_exists(__DIR__ . "/c{$i}.b64"); $i++) unlink(__DIR__ . "/c{$i}.b64");
unlink(__FILE__);
echo "Done! Now run: php public/setup_reports.php\n";
