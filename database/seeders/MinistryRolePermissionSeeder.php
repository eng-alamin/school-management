<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds permissions & roles for the Ministry Panel.
*/
class MinistryRolePermissionSeeder extends Seeder
{
    private const GUARD = 'web';
    public const PREFIX = 'ministry.';
    public const MINISTRY_TEAM_ID = 0;

    private const PERMISSIONS = [
        // Institution Oversight
        'ministry.institution.view',
        'ministry.institution.approve',      // Approval Workflow module

        // Circular / Notice Management
        'ministry.circular.view',
        'ministry.circular.manage',

        // Grievance / Complaint Management
        'ministry.grievance.view',
        'ministry.grievance.manage',

        // Compliance & Inspection
        'ministry.compliance.view',
        'ministry.compliance.manage',

        // Academic Performance / Exam Monitoring
        'ministry.exam-monitoring.view',

        // Institution Ranking
        'ministry.ranking.view',
        'ministry.ranking.manage',

        // Geographic Heatmap
        'ministry.heatmap.view',

        // Reports & Analytics
        'ministry.report.view',
        'ministry.report.export',

        // Ministry User & Role Management
        'ministry.ministry-user.view',
        'ministry.ministry-user.manage',

        // Activity Log Viewer
        'ministry.audit-log.view',
    ];

    private const ROLE_PERMISSION_MAP = [
        'Ministry Super Admin' => '*',

        'Ministry Approver' => [
            'ministry.institution.view',
            'ministry.institution.approve',
            'ministry.report.view',
        ],

        'Ministry Inspector' => [
            'ministry.compliance.view',
            'ministry.compliance.manage',
            'ministry.grievance.view',
            'ministry.grievance.manage',
            'ministry.exam-monitoring.view',
            'ministry.report.view',
        ],

        'Ministry Finance Officer' => [
            'ministry.report.view',
            'ministry.report.export',
            // ভবিষ্যতে MPO/Scholarship/Financial-Audit permission এখানে যোগ হবে
        ],

        'Ministry Viewer' => [
            'ministry.institution.view',
            'ministry.compliance.view',
            'ministry.grievance.view',
            'ministry.exam-monitoring.view',
            'ministry.ranking.view',
            'ministry.heatmap.view',
            'ministry.report.view',
            'ministry.circular.view',
        ],
    ];

    public function run(): void
    {

        app(PermissionRegistrar::class)->setPermissionsTeamId(self::MINISTRY_TEAM_ID);

        foreach (self::PERMISSIONS as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => self::GUARD],
                // ['institution_id' => self::MINISTRY_TEAM_ID]
            );
        }

        foreach (self::ROLE_PERMISSION_MAP as $roleName => $permissionNames) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => self::GUARD],
                ['institution_id' => self::MINISTRY_TEAM_ID]
            );

            if ($role->institution_id !== self::MINISTRY_TEAM_ID) {
                $role->forceFill(['institution_id' => self::MINISTRY_TEAM_ID])->save();
            }

            if ($permissionNames === '*') {
                $role->syncPermissions(self::PERMISSIONS);
            } else {
                $role->syncPermissions($permissionNames);
            }
        }

        // Permission::where('name', 'like', self::PREFIX . '%')
        //     ->whereNull('institution_id')
        //     ->update(['institution_id' => self::MINISTRY_TEAM_ID]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command?->info('Ministry roles ও permissions সফলভাবে তৈরি হয়েছে।');
    }
}