<?php
/**
 * Fix Autoloader for ClassRoom Model
 * 
 * This script patches the Composer autoloader files so that
 * App\Models\ClassRoom is properly mapped to ClassRoom.php
 * instead of the old Classroom.php.
 * 
 * Run once on cPanel, then DELETE this file for security.
 */

echo "<h1>Autoloader Fix Script</h1>";
echo "<pre>";

$fixed = 0;
$errors = [];

// ── 1. Fix autoload_classmap.php ──
$classmapFile = __DIR__ . '/vendor/composer/autoload_classmap.php';
if (file_exists($classmapFile)) {
    $content = file_get_contents($classmapFile);
    
    // Check if ClassRoom entry already exists
    if (strpos($content, "'App\\\\Models\\\\ClassRoom'") !== false || 
        strpos($content, "'App\\Models\\ClassRoom'") !== false) {
        echo "✓ autoload_classmap.php already has ClassRoom entry\n";
    } else {
        // Add ClassRoom entry pointing to ClassRoom.php
        $search = "'App\\\\Models\\\\Classroom' =>";
        $replace = "'App\\\\Models\\\\ClassRoom' => \$baseDir . '/app/Models/ClassRoom.php',\n    'App\\\\Models\\\\Classroom' =>";
        
        // Try with single backslashes too
        if (strpos($content, $search) === false) {
            $search = "'App\\Models\\Classroom' =>";
            $replace = "'App\\Models\\ClassRoom' => \$baseDir . '/app/Models/ClassRoom.php',\n    'App\\Models\\Classroom' =>";
        }
        
        if (strpos($content, $search) !== false) {
            $newContent = str_replace($search, $replace, $content);
            if (file_put_contents($classmapFile, $newContent)) {
                echo "✓ Added ClassRoom entry to autoload_classmap.php\n";
                $fixed++;
            } else {
                $errors[] = "Failed to write autoload_classmap.php (permission denied?)";
            }
        } else {
            // Try another approach - find the Classroom entry and add ClassRoom before it
            if (preg_match("/('App\\\\\\\\Models\\\\\\\\Classroom'\s*=>)/", $content, $matches)) {
                $insert = "'App\\\\Models\\\\ClassRoom' => \$baseDir . '/app/Models/ClassRoom.php',\n    " . $matches[1];
                $newContent = str_replace($matches[1], $insert, $content, $count);
                if ($count > 0 && file_put_contents($classmapFile, $newContent)) {
                    echo "✓ Added ClassRoom entry to autoload_classmap.php (regex method)\n";
                    $fixed++;
                }
            } else {
                echo "! Could not find Classroom entry in autoload_classmap.php\n";
                echo "  File content sample:\n";
                echo substr($content, 0, 500) . "...\n";
            }
        }
    }
} else {
    $errors[] = "autoload_classmap.php not found at $classmapFile";
}

// ── 2. Fix autoload_static.php ──
$staticFile = __DIR__ . '/vendor/composer/autoload_static.php';
if (file_exists($staticFile)) {
    $content = file_get_contents($staticFile);
    
    if (strpos($content, "'App\\\\Models\\\\ClassRoom'") !== false ||
        strpos($content, "'App\\Models\\ClassRoom'") !== false) {
        echo "✓ autoload_static.php already has ClassRoom entry\n";
    } else {
        // Find the Classroom entry in the static classmap and add ClassRoom before it
        // The static file uses different formatting
        $patterns = [
            "/('App\\\\\\\\Models\\\\\\\\Classroom'\s*=>)/",
            "/('App\\\\Models\\\\Classroom'\s*=>)/",
        ];
        
        $found = false;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $insert = "'App\\\\Models\\\\ClassRoom' => __DIR__ . '/../..' . '/app/Models/ClassRoom.php',\n        " . $matches[1];
                $newContent = str_replace($matches[1], $insert, $content, $count);
                if ($count > 0 && file_put_contents($staticFile, $newContent)) {
                    echo "✓ Added ClassRoom entry to autoload_static.php\n";
                    $fixed++;
                    $found = true;
                }
                break;
            }
        }
        
        if (!$found) {
            echo "! Could not find Classroom entry in autoload_static.php\n";
            echo "  Trying alternative method...\n";
            
            // Alternative: find the prefixDirsPsr4 entry and ensure ClassRoom is loadable via PSR-4
            // This shouldn't be needed with optimized autoloader, but let's try
        }
    }
} else {
    $errors[] = "autoload_static.php not found at $staticFile";
}

// ── 3. Ensure ClassRoom.php exists and is correct ──
$classRoomFile = __DIR__ . '/app/Models/ClassRoom.php';
if (file_exists($classRoomFile)) {
    $content = file_get_contents($classRoomFile);
    if (strpos($content, 'class ClassRoom extends Model') !== false) {
        echo "✓ ClassRoom.php exists and has correct class definition\n";
    } elseif (strpos($content, 'require_once') !== false) {
        echo "✗ ClassRoom.php still has require_once - fixing it...\n";
        // Replace with the self-contained version
        $newContent = "<?php\n\nnamespace App\Models;\n\nuse Illuminate\Database\Eloquent\Factories\HasFactory;\nuse Illuminate\Database\Eloquent\Model;\n\nclass ClassRoom extends Model\n{\n    use HasFactory;\n\n    protected \$table = 'classes';\n\n    protected \$fillable = ['branch_id', 'academic_year_id', 'name', 'numeric_name', 'capacity'];\n\n    public function getCalculatedCapacityAttribute()\n    {\n        if (\$this->relationLoaded('sections') || \$this->sections()->exists()) {\n            \$sum = \$this->sections->sum('max_students');\n            if (\$sum > 0) return \$sum;\n        }\n        return \$this->capacity;\n    }\n\n    public function recalculateCapacity(): void\n    {\n        \$this->capacity = \$this->sections()->sum('max_students') ?: null;\n        \$this->saveQuietly();\n    }\n\n    public function sections()\n    {\n        return \$this->hasMany(Section::class, 'class_id')->orderBy('name');\n    }\n\n    public function students()\n    {\n        return \$this->hasMany(Student::class, 'class_id');\n    }\n\n    public function academicYear()\n    {\n        return \$this->belongsTo(AcademicYear::class);\n    }\n\n    public function branch()\n    {\n        return \$this->belongsTo(Branch::class);\n    }\n}\n";
        if (file_put_contents($classRoomFile, $newContent)) {
            echo "✓ Fixed ClassRoom.php (removed require_once, made self-contained)\n";
            $fixed++;
        } else {
            $errors[] = "Failed to write ClassRoom.php (permission denied?)";
        }
    }
} else {
    $errors[] = "ClassRoom.php not found at $classRoomFile";
}

// ── 4. Delete Classroom.php if it exists ──
$classroomFile = __DIR__ . '/app/Models/Classroom.php';
if (file_exists($classroomFile)) {
    if (unlink($classroomFile)) {
        echo "✓ Deleted old Classroom.php alias file\n";
        $fixed++;
    } else {
        $errors[] = "Failed to delete Classroom.php (permission denied?)";
    }
} else {
    echo "✓ Classroom.php doesn't exist (good)\n";
}

// ── Summary ──
echo "\n═══════════════════════════════════════\n";
echo "Fixed: $fixed item(s)\n";
if ($errors) {
    echo "Errors:\n";
    foreach ($errors as $err) {
        echo "  ✗ $err\n";
    }
}
echo "═══════════════════════════════════════\n\n";

if ($fixed > 0) {
    echo "✅ Autoloader patched! Try your site now.\n";
    echo "⚠️ IMPORTANT: DELETE this file (fix-autoloader.php) after confirming it works!\n";
} else if (empty($errors)) {
    echo "✅ Everything looks good already. The issue may be elsewhere.\n";
}

echo "</pre>";
