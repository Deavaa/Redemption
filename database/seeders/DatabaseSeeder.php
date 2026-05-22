<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Core admin user + settings (must be first)
            \Database\Seeders\DemoAdminSeeder::class,
            \Database\Seeders\SettingsSeeder::class,

            // 2. Permissions & roles (must be before users who need roles)
            \Database\Seeders\PermissionSeeder::class,

            // 3. Full school data (branches, AY, terms, teachers, subjects, classes,
            //    sections, real Lebu students G9-12, assignments, events, grades)
            \Database\Seeders\SchoolDataSeeder::class,
        ]);
    }
}
