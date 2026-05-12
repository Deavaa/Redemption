<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ["name" => "System Administrator", "email" => "admin@schoolofredemption.com", "password" => "password", "role" => "admin"],
            ["name" => "Mr. John Smith", "email" => "john.smith@schoolofredemption.com", "password" => "password", "role" => "teacher"],
            ["name" => "Ms. Mary Johnson", "email" => "mary.johnson@schoolofredemption.com", "password" => "password", "role" => "teacher"],
            ["name" => "Mr. Peter Williams", "email" => "peter.williams@schoolofredemption.com", "password" => "password", "role" => "staff"],
        ];

        foreach ($users as $u) {
            User::updateOrCreate(
                ["email" => $u["email"]], 
                [
                    "name" => $u["name"], 
                    "password" => Hash::make($u["password"]), 
                    "role" => $u["role"], 
                    "is_active" => true
                ]
            );
        }
    }
}
