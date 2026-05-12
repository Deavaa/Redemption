<?php
 $f = file_get_contents("resources/views/layouts/admin.blade.php");
 $f = str_replace("{{ route('logout') }}", "/logout", $f);
file_put_contents("resources/views/layouts/admin.blade.php", $f);
echo "Layout fixed - using /logout URL directly.\n";
