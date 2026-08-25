<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Spatie's `teams` feature uses `institution_id` (configured as the team
 * foreign key in config/permission.php) on the model_has_roles and
 * model_has_permissions pivot tables.
 *
 * Institution-level roles are correctly scoped to an institution_id.
 * BUT Ministry Panel roles/permissions are GLOBAL — they are not tied to
 * any single institution — so institution_id must be nullable, otherwise
 * assignRole()/givePermissionTo() for a Ministry user throws:
 *   "Column 'institution_id' cannot be null"
 *
 * This migration only relaxes the NOT NULL constraint. It does NOT change
 * any existing data, and does not affect Institution role assignment
 * (which will keep supplying a real institution_id as before).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('institution_id')->nullable()->change();
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('institution_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Guard: refuse to roll back if any NULL institution_id rows exist
        // (Ministry role assignments), since re-adding NOT NULL would fail.
        $hasNullRoles = DB::table('model_has_roles')->whereNull('institution_id')->exists();
        $hasNullPerms = DB::table('model_has_permissions')->whereNull('institution_id')->exists();

        if ($hasNullRoles || $hasNullPerms) {
            throw new \RuntimeException(
                'Cannot roll back: NULL institution_id rows exist (Ministry role/permission assignments). '
                . 'Remove those rows first if you really need to revert this migration.'
            );
        }

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('institution_id')->nullable(false)->change();
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('institution_id')->nullable(false)->change();
        });
    }
};
