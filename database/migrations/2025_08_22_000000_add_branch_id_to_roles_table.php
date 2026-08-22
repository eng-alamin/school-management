<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a nullable `branch_id` column to the Spatie `roles` table.
     *
     * Semantics:
     *  - branch_id = NULL  => institution-wide role (visible/usable across all branches)
     *  - branch_id = <id>  => role scoped to that specific branch only
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        throw_if(empty($tableNames), Exception::class, 'Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('institution_id');

            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();

            $table->index(['institution_id', 'branch_id'], 'roles_institution_branch_idx');
        });

        // NOTE: Unique constraint decision pending confirmation from Al Amin.
        // If Option A (per-branch distinct role names) is confirmed, uncomment below
        // and remove the old unique constraint first:
        //
        // Schema::table($tableNames['roles'], function (Blueprint $table) use ($tableNames) {
        //     $table->dropUnique([$tableNames['roles'] === 'roles' ? 'roles_institution_id_name_guard_name_unique' : $tableNames['roles'] . '_institution_id_name_guard_name_unique']);
        //     $table->unique(['institution_id', 'branch_id', 'name', 'guard_name'], 'roles_institution_branch_name_guard_unique');
        // });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropIndex('roles_institution_branch_idx');
            $table->dropColumn('branch_id');
        });
    }
};
