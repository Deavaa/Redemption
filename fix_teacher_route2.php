<?php
echo "Fixing teachers route namespace...\n";
 $r = file_get_contents('routes/web.php');
// Fix short reference to full namespace
 $r = str_replace(
  "Teacher\\TeacherController",
  "App\\Http\\Controllers\\Teacher\\TeacherController",
  $r
);
file_put_contents('routes/web.php',$r);
echo "DONE\n";
