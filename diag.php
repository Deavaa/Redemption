<?php
 $dir = app_path('Http/Controllers/AcademicYear');
if(!is_dir($dir)) mkdir($dir,0755,true);
echo "Dir: $dir\n";
 $files = glob($dir.'/*.php');
echo "Files: ".count($files)."\n";
foreach($files as $f) echo "  $f\n";

echo "\n=== Route check ===\n";
require 'vendor/autoload.php';
 $app = require 'bootstrap/app.php';
 $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
 $kernel->bootstrap();
 $routes = app('router')->getRoutes();
foreach($routes as $r) {
    $uri = $r->uri();
    if(strpos($uri,'academic-years') !== false) {
        $action = $r->getAction();
        echo "URI: $uri\n";
        echo "Uses: ".($action['uses'] ?? 'closure')."\n";
        echo "Controller: ".($action['controller'] ?? 'none')."\n\n";
    }
}

echo "=== All controller directories ===\n";
 $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path('Http/Controllers')));
foreach($rii as $f) {
    if($f->isFile() && strpos($f->getFilename(),'AcademicYear') !== false) {
        echo "FOUND: ".$f->getPathname()."\n";
        $c = file_get_contents($f->getPathname());
        if(strpos($c,'function edit') !== false) {
            echo "  HAS edit method\n";
            if(strpos($c,"compact('data'") !== false) {
                echo "  HAS compact data - OK\n";
            } else {
                echo "  MISSING compact data - BROKEN\n";
            }
        }
    }
}
