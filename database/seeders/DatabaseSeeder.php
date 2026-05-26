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

            // 2. System settings
            \Database\Seeders\SettingsSeeder::class,

            // 3. Permissions & roles (must be before users who need roles)
            \Database\Seeders\PermissionSeeder::class,

            // 4. School infrastructure (Lebu branch, AY, terms, classes, sections)
            //    + grade scales — NO sample/mock data
            //    Add Tuludimtu branch/classes, students, teachers, subjects via UI
            \Database\Seeders\SchoolDataSeeder::class,
        ]);
    }
}
