<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
k = $app->make('Illuminate\Contracts\Console\Kernel');
$k->bootstrap();
$cols = DB::select('SHOW COLUMNS FROM users');
echo 'Table columns: ' . $cols[0]->Columns.PHP_EOL;