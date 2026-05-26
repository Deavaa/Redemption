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

            // 4. School infrastructure (branches, AY, terms, classes, sections)
            //    + 121 real Tuludimtu students + enrollments + grade scales
            //    NO sample/mock data — add teachers, subjects, Lebu students via UI
            \Database\Seeders\SchoolDataSeeder::class,
        ]);
    }
}
