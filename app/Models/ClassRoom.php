<?php

namespace App\Models;

/**
 * ClassRoom – backward-compatible alias for the Classroom model.
 *
 * The canonical model is App\Models\Classroom (file: Classroom.php),
 * but the codebase widely references ClassRoom (capital R).
 * On Windows (case-insensitive autoloader) both resolve to the same file,
 * but on Linux they would not.  This alias ensures ClassRoom works
 * everywhere without changing hundreds of references.
 *
 * Do NOT add additional logic here – put it in Classroom instead.
 */
class ClassRoom extends Classroom
{
    // Intentionally empty. All logic lives in Classroom.
}
