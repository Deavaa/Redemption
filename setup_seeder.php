<?php
echo "Creating seeder...\n";
 $c = <<<'S'
<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\User;
class DemoAdminSeeder extends Seeder {
    public function run() {
        User::where('email','admin@school.com')->delete();
        User::create([
            'name'=>'Admin User',
            'email'=>'admin@school.com',
            'password'=>bcrypt('password'),
            'role'=>'admin',
            'email_verified_at'=>now(),
        ]);
        $this->command->info('Demo admin: admin@school.com / password');
    }
}
S;
@mkdir('database/seeders',0755,true);
file_put_contents('database/seeders/DemoAdminSeeder.php',$c);
 $c2 = <<<'S2'
<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder {
    public function run() {
        $this->call([\Database\Seeders\DemoAdminSeeder::class]);
    }
}
S2;
file_put_contents('database/seeders/DatabaseSeeder.php',$c2);
echo "DONE: Seeder files created\n";
