<?php
echo "Adding logout route...\n";
 $r = file_get_contents('routes/web.php');
if(strpos($r,"Route::post('logout'")===false){
 $r = str_replace("Route::get('/login'",
"Route::post('logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');\nRoute::get('/login'",$r);
file_put_contents('routes/web.php',$r);
echo "DONE: logout route added\n";
} else { echo "logout route already exists\n"; }
