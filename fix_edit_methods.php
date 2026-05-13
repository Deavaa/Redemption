<?php
require 'vendor/autoload.php';
 $app = require_once 'bootstrap/app.php';

 $ctrlDir = app_path('Http/Controllers');

// Map: folder/controller name -> model name
 $modelMap = [
    'AcademicYear' => 'App\Models\AcademicYear',
    'Term' => 'App\Models\Term',
    'Subject' => 'App\Models\Subject',
    'Teacher' => 'App\Models\Teacher',
    'Classroom' => 'App\Models\Classroom',
    'Student' => 'App\Models\Student',
    'Branch' => 'App\Models\Branch',
    'Exam' => 'App\Models\Exam',
    'Fee' => 'App\Models\Fee',
    'GalleryImage' => 'App\Models\GalleryImage',
    'GalleryVideo' => 'App\Models\GalleryVideo',
    'Slider' => 'App\Models\Slider',
    'Setting' => 'App\Models\Setting',
    'TeamMember' => 'App\Models\TeamMember',
    'Message' => 'App\Models\ContactMessage',
];

 $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($ctrlDir));
 $fixed = 0;

foreach($rii as $file) {
    if(!$file->isFile() || $file->getExtension() != 'php') continue;
    if(strpos($file->getFilename(), 'Controller') === false) continue;
    
    $path = $file->getPathname();
    $content = file_get_contents($path);
    
    if(strpos($content, 'function edit') === false) continue;
    if(strpos($content, "compact('data'") !== false) continue;
    
    // Find controller class name
    preg_match('/class\s+(\w+)\s+Controller/', $content, $m);
    if(!$m) continue;
    $name = $m[1];
    
    // Determine model
    $model = $modelMap[$name] ?? 'App\\Models\\'.$name;
    
    // Detect view folder name (kebab-case or PascalCase)
    preg_match('/admin\.([\w-]+)\.edit/', $content, $vm);
    $viewFolder = $vm[1] ?? $name;
    
    // Detect route name prefix
    preg_match('/admin\.([\w-]+)\./', $content, $rm);
    $routePrefix = $rm[1] ?? strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $name));
    
    echo "Fixing: $name -> Model: $model, View: admin.$viewFolder.edit, Route: admin.$routePrefix\n";
    
    // Build replacement edit method
    $newEdit = "    public function edit(\$id)\n    {\n        \$data = $model::findOrFail(\$id);\n        return view('admin.$viewFolder.edit', compact('data'));\n    }";
    
    // Replace the edit method
    $content = preg_replace(
        '/public\s+function\s+edit\s*\([^)]*\)\s*\{[^}]*(?:\{[^}]*\}[^}]*)*\}/s',
        $newEdit,
        $content,
        1,
        $count
    );
    
    if($count) {
        file_put_contents($path, $content);
        echo "  FIXED: $path\n";
        $fixed++;
    }
}

echo "\nTotal fixed: $fixed controllers\n";
