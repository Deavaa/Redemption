<?php

/**
 * BACKWARD COMPATIBILITY — ClassRoom alias for shared hosting
 *
 * On shared hosting (ByetHost/cPanel), the Composer optimized autoloader
 * classmap may only reference ONE of "Classroom" or "ClassRoom" depending
 * on when "composer dump-autoload" was last run. Since we can't regenerate
 * the autoloader on shared hosting, this file ensures BOTH class names
 * resolve to the same model.
 *
 * WHY require_once + class_alias (NOT "extends"):
 *   Using "class ClassRoom extends Classroom" requires the autoloader to
 *   find App\Models\Classroom when it encounters "extends Classroom".
 *   But the optimized autoloader classmap may NOT have an entry for
 *   App\Models\Classroom if it was generated when only ClassRoom existed.
 *   require_once bypasses the autoloader entirely — it loads the file
 *   directly by path, then class_alias() registers the alternate name.
 *
 * Both of these now work everywhere:
 *   use App\Models\ClassRoom;   ← works (via this file + class_alias)
 *   use App\Models\Classroom;   ← works (via autoloader classmap OR PSR-4)
 */

// Explicitly load the real model file — bypass the autoloader entirely
require_once __DIR__ . '/Classroom.php';

// Now App\Models\Classroom is defined. Create the alias.
class_alias('App\Models\Classroom', 'App\Models\ClassRoom');
