<?php
echo "Checking for teacher/staff table...\n";
require 'vendor/autoload.php';
require 'bootstrap/app.php';
 $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
 $kernel->bootstrap();
 $tables = \Illuminate\Support\Facades\Schema::getTableListing();
echo "Tables matching teacher/staff:\n";
foreach($tables as $t){
  if(stripos($t,'teacher')!==false || stripos($t,'staff')!==false){
    echo "  FOUND: $t\n";
  }
}
if(!in_array('teachers',$tables)){
  echo "\n'teachers' table not found! Checking migrations...\n";
  $mfiles = glob('database/migrations/*teacher*');
  foreach($mfiles as $f) echo "  ".basename($f)."\n";
  $mfiles = glob('database/migrations/*staff*');
  foreach($mfiles as $f) echo "  ".basename($f)."\n";
}
