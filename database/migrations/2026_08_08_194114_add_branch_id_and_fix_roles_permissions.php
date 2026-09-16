<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Combined migration (merges 3 separate migrations into one, for a fresh
 * install where none of them have run yet):
 *
 *  1. Adds nullable `branch_id` to the Spatie `roles` table.
 *     - branch_id = NULL  => institution-wide role
 *     - branch_id = <id>  => role scoped to that specific branch only
 *
 *  2. Makes `institution_id` nullable on model_has_roles /
 *     model_has_permissions, because Ministry Panel roles/permissions are
 *     global (not tied to any institution), so assignRole()/
 *     givePermissionTo() for a Ministry user must not require it.
 *
 *  3. Fixes the roles table's unique constraint. Spatie's base migration
 *     creates UNIQUE(institution_id [team_id], name, guard_name). Without
 *     branch_id in that constraint, two branches in the same institution
 *     both creating a role with the same name (e.g. 'branch') collide
 *     (1062 Duplicate entry '<institution_id>-<name>-<guard_name>'). This
 *     drops that constraint and replaces it with one that includes
 *     branch_id, so per-branch roles are correctly unique.
 */
return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        throw_if(empty($tableNames), Exception::class, 'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');

        $rolesTable = $tableNames['roles'];

        // ── 1. branch_id column on roles ──────────────────────────────
        Schema::table($rolesTable, function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('institution_id');

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();

            $table->index(['institution_id', 'branch_id'], 'roles_institution_branch_idx');
        });

        // ── 2. institution_id nullable on pivot tables (Ministry roles) ─
        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) {
            $table->unsignedBigInteger('institution_id')->nullable()->change();
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) {
            $table->unsignedBigInteger('institution_id')->nullable()->change();
        });

        // ── 3. Fix roles unique constraint to include branch_id ────────
        // Defensive merge in case this runs on a DB that already has some
        // data (harmless no-op on a truly fresh/empty roles table).
        $duplicates = DB::table($rolesTable)
            ->select('institution_id', 'branch_id', 'name', 'guard_name', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('institution_id', 'branch_id', 'name', 'guard_name')
            ->having('cnt', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $duplicateIds = DB::table($rolesTable)
                ->where('institution_id', $dup->institution_id)
                ->where('branch_id', $dup->branch_id)
                ->where('name', $dup->name)
                ->where('guard_name', $dup->guard_name)
                ->where('id', '!=', $dup->keep_id)
                ->pluck('id');

            foreach ($duplicateIds as $dupId) {
                DB::table($tableNames['model_has_roles'])
                    ->where('role_id', $dupId)
                    ->update(['role_id' => $dup->keep_id]);

                $existingPermIds = DB::table($tableNames['role_has_permissions'])
                    ->where('role_id', $dup->keep_id)
                    ->pluck('permission_id');

                DB::table($tableNames['role_has_permissions'])
                    ->where('role_id', $dupId)
                    ->whereNotIn('permission_id', $existingPermIds)
                    ->update(['role_id' => $dup->keep_id]);

                DB::table($tableNames['role_has_permissions'])->where('role_id', $dupId)->delete();

                DB::table($rolesTable)->where('id', $dupId)->delete();
            }
        }

        Schema::table($rolesTable, function (Blueprint $table) {
            $table->dropUnique('roles_institution_id_name_guard_name_unique');
            $table->unique(
                ['institution_id', 'branch_id', 'name', 'guard_name'],
                'roles_institution_branch_name_guard_unique'
            );
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');
        $rolesTable = $tableNames['roles'];

        // Guard: refuse to roll back institution_id-nullable if Ministry
        // role/permission assignments (NULL institution_id) exist.
        $hasNullRoles = DB::table($tableNames['model_has_roles'])->whereNull('institution_id')->exists();
        $hasNullPerms = DB::table($tableNames['model_has_permissions'])->whereNull('institution_id')->exists();

        if ($hasNullRoles || $hasNullPerms) {
            throw new \RuntimeException(
                'Cannot roll back: NULL institution_id rows exist (Ministry role/permission assignments). '
                . 'Remove those rows first if you really need to revert this migration.'
            );
        }

        Schema::table($rolesTable, function (Blueprint $table) {
            $table->dropUnique('roles_institution_branch_name_guard_unique');
            $table->unique(
                ['institution_id', 'name', 'guard_name'],
                'roles_institution_id_name_guard_name_unique'
            );

            $table->dropForeign(['branch_id']);
            $table->dropIndex('roles_institution_branch_idx');
            $table->dropColumn('branch_id');
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) {
            $table->unsignedBigInteger('institution_id')->nullable(false)->change();
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) {
            $table->unsignedBigInteger('institution_id')->nullable(false)->change();
        });
    }
};
