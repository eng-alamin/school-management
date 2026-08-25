<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds permissions & roles for the Ministry Panel.
 *
 * IMPORTANT — GUARD ISOLATION:
 * Ministry Panel uses the SAME `App\Models\User` model as Institution
 * users, but a SEPARATE guard: 'ministry' (see config/auth.php).
 *
 * This is intentional and critical: Institution roles/permissions are
 * seeded under guard_name = 'web' (see InstitutionRolePermissionSeeder).
 * If Ministry permissions used the same 'web' guard, a query like
 * `Permission::where('guard_name', 'web')->get()` would return BOTH
 * Institution AND Ministry permissions together — causing "Ministry
 * Super Admin" to accidentally receive full Institution-level access
 * (branch.*, student.*, employee.*, etc.). Using a dedicated 'ministry'
 * guard makes that leak structurally impossible.
 *
 * Ministry roles are NOT team-scoped (Ministry oversight is not tied
 * to a single institution), so we explicitly clear any team context
 * before creating roles.
 *
 * Idempotent (firstOrCreate) — safe to re-run.
 */
class MinistryRolePermissionSeeder extends Seeder
{
    /**
     * Guard used by the Ministry Panel.
     *
     * NOTE: This project uses a SINGLE session guard ('web') for every
     * panel (Admin, Ministry, etc.) — differentiated by the `role` column
     * on `users` + the custom App\Http\Middleware\RoleMiddleware, not by
     * separate Laravel auth guards. Blade's @can()/Gate::allows() always
     * resolve permissions against the CURRENTLY authenticated guard
     * (here: 'web'). So Ministry permissions/roles MUST also be seeded
     * under guard_name = 'web', or @can() will always return false.
     *
     * Isolation from Institution permissions (so "Ministry Super Admin"
     * can never accidentally receive branch.*, student.*, etc.) is instead
     * enforced by using an EXPLICIT permission list (self::PERMISSIONS)
     * for the '*' role, never a blanket `Permission::where('guard_name',
     * 'web')->get()` query — see run() below.
     */
    private const GUARD = 'web';

    /**
     * Sentinel "team" id representing "no institution / global scope".
     *
     * WHY NOT NULL: model_has_roles / model_has_permissions use a
     * COMPOSITE PRIMARY KEY that includes institution_id. MySQL forces
     * every column inside a PRIMARY KEY to be NOT NULL — this is a MySQL
     * engine rule, `nullable()` in a migration cannot override it for a
     * PK column. So NULL can never be stored there, regardless of the
     * column's nullable flag.
     *
     * Institutions auto-increment from 1, so `0` can never collide with
     * a real institution and is safe to use as the "global" marker.
     */
    public const GLOBAL_TEAM_ID = 0;

    /**
     * Ministry Panel permission list.
     * Naming pattern: {module}.{action}
     *
     * নতুন module (MPO, Scholarship, Textbook, ইত্যাদি) যোগ হলে
     * এখানে নতুন লাইন যোগ করলেই হবে — migration লাগবে না।
     */
    private const PERMISSIONS = [
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

        // Ministry User & Role Management
        'ministry-user.view',
        'ministry-user.manage',

        // Activity Log Viewer
        'audit-log.view',
    ];

    /**
     * প্রাথমিক role গুলো এবং তাদের permission mapping।
     * '*' মানে — শুধু GUARD-এর অধীনে থাকা সব permission (cross-guard নয়)।
     */
    private const ROLE_PERMISSION_MAP = [
        'Ministry Super Admin' => '*',

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
        // Ministry roles are global (not tied to a single institution).
        // Use the GLOBAL_TEAM_ID sentinel (0) — NOT null — because
        // model_has_roles/model_has_permissions store institution_id as
        // part of a composite PRIMARY KEY, and MySQL never allows NULL
        // inside a PRIMARY KEY column.
        app(PermissionRegistrar::class)->setPermissionsTeamId(self::GLOBAL_TEAM_ID);

        foreach (self::PERMISSIONS as $permissionName) {
            Permission::firstOrCreate([
                'name'       => $permissionName,
                'guard_name' => self::GUARD,
            ]);
        }

        foreach (self::ROLE_PERMISSION_MAP as $roleName => $permissionNames) {
            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => self::GUARD,
            ]);

            if ($permissionNames === '*') {
                // CRITICAL: do NOT do Permission::where('guard_name', 'web')
                // ->get() here. Since Institution permissions (branch.*,
                // student.*, employee.*, ...) also live under guard 'web'
                // in this project, that query would hand "Ministry Super
                // Admin" full Institution-level access too. Instead, sync
                // only the explicit Ministry permission list defined above.
                $role->syncPermissions(self::PERMISSIONS);
            } else {
                // Spatie resolves these by name; since $role's guard_name
                // is 'ministry', it will only match 'ministry' permissions.
                $role->syncPermissions($permissionNames);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->command->info('Ministry roles ও permissions সফলভাবে তৈরি হয়েছে।');
    }
}