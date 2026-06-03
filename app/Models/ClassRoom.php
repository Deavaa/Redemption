<?php

/**
 * COMPATIBILITY ALIAS — Makes "ClassRoom" work alongside "Classroom"
 *
 * PROBLEM: The deployed code on ByetHost uses "use App\Models\ClassRoom;"
 * in controllers, but the Composer optimized autoloader classmap only
 * has "App\Models\Classroom" mapped to Classroom.php.
 *
 * SOLUTION: This file exists at the PSR-4 expected path for "ClassRoom".
 * When the autoloader fails to find "ClassRoom" in the classmap, it
 * falls back to PSR-4 which looks for ClassRoom.php — this file.
 * We then immediately alias "ClassRoom" to the real "Classroom" class.
 *
 * Both names now work:
 *   use App\Models\ClassRoom;   ← works (via this alias file)
 *   use App\Models\Classroom;  ← works (via autoloader classmap)
 */
class_alias('App\Models\Classroom', 'App\Models\ClassRoom');
