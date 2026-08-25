<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            'name' => 'Super Admin',
            'email' => 'sadmin@demo.com',
            'role' => 'super_admin',
            'email_verified_at' => now(),
            'password' => '12345678',
        ]);

        $ministry = User::create([
            'name'              => 'Ministry User',
            'email'             => 'ministry@demo.com',
            'role'              => 'ministry',
            'email_verified_at' => now(),
            'password'          => '12345678',
        ]);

        // This project has a single session guard ('web') for every panel,
        // so Ministry roles are seeded under guard_name = 'web' too (see
        // MinistryRolePermissionSeeder::GUARD). Passing a plain string to
        // assignRole() would still work here since there's only one guard,
        // but we fetch the Role explicitly for clarity and to fail loudly
        // (firstOrFail) if the seeder hasn't run yet.
        $ministryRole = Role::where('name', 'Ministry Super Admin')
            ->where('guard_name', 'web')
            ->firstOrFail();

        // IMPORTANT: model_has_roles uses institution_id as part of a
        // COMPOSITE PRIMARY KEY, so it can never be NULL (MySQL rule).
        // Ministry roles were seeded under the GLOBAL_TEAM_ID sentinel
        // (0) — see MinistryRolePermissionSeeder::GLOBAL_TEAM_ID. We must
        // set the same team context here before assigning, otherwise
        // Spatie will try to insert institution_id = NULL and MySQL will
        // reject it with "Column 'institution_id' cannot be null".
        app(PermissionRegistrar::class)->setPermissionsTeamId(
            MinistryRolePermissionSeeder::GLOBAL_TEAM_ID
        );

        $ministry->assignRole($ministryRole);

        // Reset team context back to null so it doesn't leak into any
        // Institution-scoped seeding that might run after this in the
        // same request/process (e.g. DatabaseSeeder calling multiple
        // seeders in sequence).
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }
}