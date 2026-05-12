<?php
 $r = file_get_contents("routes/web.php");
if (strpos($r, "logout") === false) {
    $r = str_replace(
        "Route::post('/login'",
        "Route::post('/logout', [LoginController::class, 'logout'])->name('logout');\nRoute::post('/login'",
        $r
    );
    file_put_contents("routes/web.php", $r);
    echo "Added logout route.\n";
} else {
    echo "logout text found in routes.\n";
}
