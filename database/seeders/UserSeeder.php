<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'     => 'Super Admin',
            'username' => 'sadmin',
            'email'    => 'sadmin@demo.com',
            'role'     => 'super_admin',
            'email_verified_at' => now(),
            'password' => '12345678',
        ]);

        DB::transaction(function () {
            $ministry = User::create([
                'name'              => 'Ministry User',
                'username'          => 'ministry',
                'email'             => 'ministry@demo.com',
                'role'              => 'ministry',
                'email_verified_at' => now(),
                'password'          => '12345678',
            ]);

            app(PermissionRegistrar::class)->setPermissionsTeamId(
                MinistryRolePermissionSeeder::MINISTRY_TEAM_ID
            );

            $ministryRole = Role::where('name', 'Ministry Super Admin')->where('guard_name', 'web')->firstOrFail();
            $ministry->assignRole($ministryRole);

            app(PermissionRegistrar::class)->setPermissionsTeamId(null);
        });
    }
}