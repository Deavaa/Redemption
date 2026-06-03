<?php

namespace App\Models;

/**
 * BACKWARD COMPATIBILITY — ClassRoom alias for shared hosting
 *
 * On shared hosting (ByetHost/cPanel), the Composer optimized autoloader
 * classmap may reference either "Classroom" or "ClassRoom" depending on
 * when "composer dump-autoload" was last run. Since we can't regenerate
 * the autoloader on shared hosting, this file ensures BOTH class names
 * resolve to the same model.
 *
 * The actual class definition is in Classroom.php.
 * This class simply extends Classroom with no overrides.
 *
 * Both of these now work everywhere:
 *   use App\Models\ClassRoom;   ← works (via this file)
 *   use App\Models\Classroom;   ← works (via autoloader classmap)
 */
class ClassRoom extends Classroom
{
    // Intentionally empty — all logic is in Classroom.php
}
