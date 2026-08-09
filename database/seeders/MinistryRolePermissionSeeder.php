<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MinistryRolePermissionSeeder extends Seeder
{
    /**
     * Ministry Panel-এর জন্য permission list।
     * Naming pattern: {module}.{action}
     *
     * নতুন module (MPO, Scholarship, Textbook, ইত্যাদি) যোগ হলে
     * এখানে নতুন লাইন যোগ করলেই হবে — migration লাগবে না।
     */
    protected array $permissions = [
        // Institution Oversight
        'institution.view',
        'institution.approve',      // Approval Workflow module

        // Circular / Notice Management
        'circular.view',
        'circular.manage',

        // Grievance / Complaint Management
        'grievance.view',
        'grievance.manage',

        // Compliance & Inspection
        'compliance.view',
        'compliance.manage',

        // Academic Performance / Exam Monitoring
        'exam-monitoring.view',

        // Institution Ranking
        'ranking.view',
        'ranking.manage',

        // Geographic Heatmap
        'heatmap.view',

        // Reports & Analytics
        'report.view',
        'report.export',

        // Ministry User & Role Management (নিজেই একটা module)
        'ministry-user.view',
        'ministry-user.manage',

        // Activity Log Viewer
        'audit-log.view',
        
    ];

    /**
     * প্রাথমিক role গুলো এবং তাদের permission mapping।
     */
    protected array $rolePermissionMap = [
        'Ministry Super Admin' => '*', // সব permission

        'Ministry Approver' => [
            'institution.view',
            'institution.approve',
            'report.view',
        ],

        'Ministry Inspector' => [
            'compliance.view',
            'compliance.manage',
            'grievance.view',
            'grievance.manage',
            'exam-monitoring.view',
            'report.view',
        ],

        'Ministry Finance Officer' => [
            'report.view',
            'report.export',
            // ভবিষ্যতে MPO/Scholarship/Financial-Audit permission এখানে যোগ হবে
        ],

        'Ministry Viewer' => [
            'institution.view',
            'compliance.view',
            'grievance.view',
            'exam-monitoring.view',
            'ranking.view',
            'heatmap.view',
            'report.view',
            'circular.view',
        ],
    ];

    public function run(): void
    {
        // Spatie-এর internal permission cache clear করা জরুরি, নাহলে পুরনো data দেখাতে পারে
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions as $permissionName) {
            Permission::firstOrCreate([
                'name'       => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        foreach ($this->rolePermissionMap as $roleName => $permissionNames) {
            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'web',
            ]);

            if ($permissionNames === '*') {
                $role->syncPermissions(Permission::where('guard_name', 'web')->get());
            } else {
                $role->syncPermissions($permissionNames);
            }
        }

        $this->command->info('Ministry roles ও permissions সফলভাবে তৈরি হয়েছে।');
    }
}