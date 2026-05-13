<?php
echo "Fixing hardcoded URLs...\n";
 $v = 'resources/views/layouts/admin.blade.php';
 $c = file_get_contents($v);
// Simple string replace for all /admin/ links
 $c = str_replace('href="/admin/', "href='{{url(\"admin/", $c);
 $c = str_replace('" class="', "\")}}' class=\"", $c);
// Fix View Website link
 $c = str_replace('href="/" target', "href='{{url(\"/\")}}' target", $c);
// Fix topbar button too
file_put_contents($v, $c);
echo "Fixed: $v\n";
// Fix dashboard
 $v2 = 'resources/views/admin/dashboard.blade.php';
 $c2 = file_get_contents($v2);
 $c2 = str_replace('href="/admin/', "href='{{url(\"admin/", $c2);
 $c2 = str_replace('" class="', "\")}}' class=\"", $c2);
file_put_contents($v2, $c2);
echo "Fixed: $v2\n";
echo "DONE\n";
