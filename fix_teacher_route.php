<?php
echo "Adding teachers route...\n";
 $r = file_get_contents('routes/web.php');
if(strpos($r,"Route::resource('teachers'")===false){
  // Add teachers route after teacher-assignments
  $r = str_replace(
    "Route::resource('teacher-assignments'",
    "Route::resource('teachers', Teacher\\TeacherController::class);\n    Route::resource('teacher-assignments'",
    $r
  );
  file_put_contents('routes/web.php',$r);
  echo "DONE: teachers route added\n";
} else {
  echo "Route already exists\n";
}
