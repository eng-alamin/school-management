<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'sadmin@demo.com',
            'role' => 'super_admin',
            'email_verified_at' => now(),
            'password' => '12345678',
        ]);
        $ministry = User::create([
            'name' => 'Ministry User',
            'email' => 'ministry@demo.com',
            'role' => 'ministry',
            'email_verified_at' => now(),
            'password' => '12345678',
        ]);
        $ministry->assignRole('Ministry Super Admin');
    }
}
