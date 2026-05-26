<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Core admin user (must be first)
            \Database\Seeders\DemoAdminSeeder::class,

            // 2. System settings & web content
            \Database\Seeders\SettingsSeeder::class,
        ]);
    }
}
