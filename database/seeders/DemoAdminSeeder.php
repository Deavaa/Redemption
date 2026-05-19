<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
class DemoAdminSeeder extends Seeder {
    public function run() {
        User::updateOrCreate(
            ['email' => 'admin@school.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('123456'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('Demo admin: admin@school.com / 123456');
    }
}