<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
class DemoAdminSeeder extends Seeder {
    public function run() {
        $user = User::withTrashed()->where('email', 'admin@school.com')->first();
        if ($user) {
            $user->restore();
            $user->update([
                'name' => 'Admin User',
                'password' => bcrypt('123456'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
        } else {
            User::create([
                'name' => 'Admin User',
                'email' => 'admin@school.com',
                'password' => bcrypt('123456'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);
        }
        $this->command->info('Demo admin: admin@school.com / 123456');
    }
}