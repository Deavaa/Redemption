<?php
require 'vendor/autoload.php';

app_use('illuminate/support/facades');
$a = require_once 'bootstrap/app.php';
k = $a->make('Illuminate\Contracts\Console\Kernel');
$k->bootstrap();
$u = DB::table('users')->where('email','admin@schoolofredemption.com')->first();
$test = Hash::check('password', $u->password);
echo 'Password matches: ' . ($test ? 'YES' : 'NO') . PHP_EOL;
echo 'User role: ' . $u->role . PHP_EOL;
echo 'Is_active: ' . $u->is_active . PHP_EOL;