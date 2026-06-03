<?php

/**
 * ALIAS FILE — Classroom → ClassRoom
 *
 * The canonical model is in ClassRoom.php. This file exists ONLY for
 * backward compatibility with the Composer autoloader classmap, which
 * may have an entry like:
 *   'App\Models\Classroom' => 'app/Models/Classroom.php'
 *
 * If the autoloader loads THIS file first (because the classmap points
 * here), we load ClassRoom.php and create the alias.
 *
 * If ClassRoom.php was already loaded (more common), the class_alias
 * call in that file already registered the alias, and this file is
 * never touched.
 */

// Load the real model — bypass the autoloader entirely
require_once __DIR__ . '/ClassRoom.php';

// Ensure the alias exists (safe to call even if already aliased)
if (!class_exists('App\Models\Classroom')) {
    class_alias('App\Models\ClassRoom', 'App\Models\Classroom');
}
