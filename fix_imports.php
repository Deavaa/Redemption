<?php
echo "Adding Controller import to subdirectory controllers...\n";
 $dirs = glob('app/Http/Controllers/*',GLOB_ONLYDIR);
 $n = 0;
foreach($dirs as $dir){
  $name = basename($dir);
  if($name === '.' || $name === '..') continue;
  $files = glob($dir.'/*Controller.php');
  foreach($files as $file){
    $c = file_get_contents($file);
    $ns = "namespace App\\Http\\Controllers\\$name;";
    if(strpos($c,$ns) !== false && strpos($c,'use App\\Http\\Controllers\\Controller;') === false){
      $c = str_replace($ns, $ns."\nuse App\\Http\\Controllers\\Controller;", $c);
      file_put_contents($file, $c);
      echo "Fixed: $file\n";
      $n++;
    }
  }
}
echo "DONE: Fixed $n controllers\n";
