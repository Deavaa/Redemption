<?php
echo "=== FIX 419 CSRF ERROR ===\n\n";
 $base = getcwd();
 $envFile = $base . '/.env';
if (!file_exists($envFile)) die("ERROR: .env not found\n");
 $env = file_get_contents($envFile);
preg_match('/^APP_URL=(.*)$/m', $env, $m);
 $appUrl = isset($m[1]) ? trim($m[1]) : '';
preg_match('/^SESSION_DOMAIN=(.*)$/m', $env, $m);
 $sessionDomain = isset($m[1]) ? trim($m[1]) : 'NOTSET';
preg_match('/^SESSION_DRIVER=(.*)$/m', $env, $m);
 $sessionDriver = isset($m[1]) ? trim($m[1]) : 'file';
preg_match('/^SESSION_LIFETIME=(.*)$/m', $env, $m);
 $sessionLife = isset($m[1]) ? trim($m[1]) : '120';
echo "Current APP_URL: $appUrl\n";
echo "Current SESSION_DOMAIN: $sessionDomain\n";
echo "Current SESSION_DRIVER: $sessionDriver\n";
echo "Current SESSION_LIFETIME: $sessionLife\n\n";
 $changed = 0;
if (empty($appUrl) || strpos($appUrl, 'example.com') !== false) {
    $env = preg_replace('/^APP_URL=.*$/m', 'APP_URL=http://localhost', $env);
    echo "FIXED: APP_URL set to http://localhost\n"; $changed++;
}
if ($sessionDomain !== 'NOTSET' && $sessionDomain !== '') {
    $env = preg_replace('/^SESSION_DOMAIN=.*$/m', 'SESSION_DOMAIN=', $env);
    echo "FIXED: SESSION_DOMAIN cleared - THIS WAS LIKELY THE 419 CAUSE\n"; $changed++;
} elseif ($sessionDomain === 'NOTSET') {
    if (strpos($env, 'SESSION_DOMAIN') === false) { $env .= "\nSESSION_DOMAIN=\n"; }
    else { $env = preg_replace('/^SESSION_DOMAIN=.*$/m', 'SESSION_DOMAIN=', $env); }
    echo "FIXED: Added SESSION_DOMAIN= (empty)\n"; $changed++;
}
if ($sessionDriver !== 'file') {
    $env = preg_replace('/^SESSION_DRIVER=.*$/m', 'SESSION_DRIVER=file', $env);
    echo "FIXED: SESSION_DRIVER set to file\n"; $changed++;
}
if ((int)$sessionLife < 120) {
    $env = preg_replace('/^SESSION_LIFETIME=.*$/m', 'SESSION_LIFETIME=120', $env);
    echo "FIXED: SESSION_LIFETIME set to 120\n"; $changed++;
}
if (strpos($env, 'SANCTUM_STATEFUL_DOMAINS') === false) {
    $env .= "\nSANCTUM_STATEFUL_DOMAINS=localhost\n";
    echo "FIXED: Added SANCTUM_STATEFUL_DOMAINS=localhost\n"; $changed++;
}
if ($changed > 0) { file_put_contents($envFile, $env); echo "\n$changed .env fixes applied\n"; }
else { echo ".env looks OK\n"; }
echo "\n--- Checking session.php ---\n";
 $sessFile = $base . '/config/session.php';
if (file_exists($sessFile)) {
    $sess = file_get_contents($sessFile);
    if (strpos($sess, "env('SESSION_SECURE_COOKIE', true)") !== false) {
        $sess = str_replace("env('SESSION_SECURE_COOKIE', true)", "env('SESSION_SECURE_COOKIE', false)", $sess);
        file_put_contents($sessFile, $sess);
        echo "FIXED: session secure default set to false\n";
    } else { echo "session.php OK\n"; }
}
echo "\n--- Checking cors.php ---\n";
 $corsFile = $base . '/config/cors.php';
if (file_exists($corsFile)) {
    $cors = file_get_contents($corsFile);
    if (strpos($cors, "'supports_credentials' => false") !== false) {
        $cors = str_replace("'supports_credentials' => false", "'supports_credentials' => true", $cors);
        file_put_contents($corsFile, $cors);
        echo "FIXED: CORS supports_credentials set to true\n";
    } else { echo "cors.php OK\n"; }
}
echo "\n--- Scanning for missing @csrf ---\n";
 $mc = 0;
 $sd = $base . '/resources/views';
function sc($d,&$mc){if(!is_dir($d))return;foreach(scandir($d) as $i){if($i==='.'||$i==='..')continue;$p=$d.'/'.$i;if(is_dir($p)){sc($p,$mc);continue;}if(strpos($p,'.blade.php')===false)continue;$c=file_get_contents($p);$r=str_replace(getcwd().'/','',$p);if(preg_match('/<form[^>]*method\s*=\s*["\'](?:POST|PUT|PATCH|DELETE|post|put|patch|delete)["\'][^>]*>/i',$c)){if(strpos($c,'@csrf')===false&&strpos($c,'csrf_field')===false){$c=preg_replace('/(<form[^>]*method\s*=\s*["\'](?:POST|PUT|PATCH|DELETE|post|put|patch|delete)["\'][^>]*>)/i',"$1\n                    @csrf",$c);file_put_contents($p,$c);echo "FIXED: Added @csrf to $r\n";$mc++;}}}}
sc($sd,$mc);
if($mc===0) echo "All forms have @csrf\n";
echo "\n--- Adding CSRF meta tag to layouts ---\n";
 $lf=0;
function fl($d,&$lf){if(!is_dir($d))return;foreach(scandir($d) as $i){if($i==='.'||$i==='..')continue;$p=$d.'/'.$i;if(is_dir($p)){fl($p,$lf);continue;}if(strpos($p,'.blade.php')===false)continue;$c=file_get_contents($p);$r=str_replace(getcwd().'/','',$p);if(strpos($c,'<head>')!==false&&strpos($c,'csrf-token')===false){$c=str_replace('<head>',"<head>\n    <meta name=\"csrf-token\" content=\"{{ csrf_token() }}\">",$c);file_put_contents($p,$c);echo "FIXED: Added CSRF meta to $r\n";$lf++;}}}
fl($sd,$lf);
if($lf===0) echo "All layouts have CSRF meta tag\n";
echo "\n--- Clearing caches ---\n";
foreach(['config:clear','cache:clear','view:clear','route:clear'] as $c){exec("php artisan $c 2>&1",$o,$rc);echo "  php artisan $c: ".($rc===0?"OK":"done")."\n";}
echo "\n--- Cleaning stale sessions ---\n";
 $sDir=$base.'/storage/framework/sessions';
if(is_dir($sDir)){$n=0;foreach(glob($sDir.'/*') as $f){if(is_file($f)&&(time()-filemtime($f))>1800){unlink($f);$n++;}}echo "Deleted $n stale sessions\n";}
echo "\n===========================================\n";
echo "  419 FIX COMPLETE!\n";
echo "===========================================\n";
echo "\nIMPORTANT - Do these 3 things NOW:\n";
echo "  1. RESTART Apache in XAMPP\n";
echo "  2. CLEAR browser cache (Ctrl+Shift+Delete)\n";
echo "  3. CLOSE all tabs, open NEW tab, LOG IN again\n";
