<?php
$c = file_get_contents('app/Http/Controllers/Auth/LoginController.php');
preg_match_all('/function (login|showLogin)/', $c, $m);
foreach($m as $f) {
    $s = strpos($f[0], strpos($f[0], '{'));
    echo substr($c, $s[1], 200) . "...\n";
}
