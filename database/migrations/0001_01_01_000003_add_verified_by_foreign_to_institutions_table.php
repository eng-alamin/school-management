<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the deferred foreign key: institutions.verified_by -> users.id
 *
 * This must run AFTER the users table migration, since institutions is
 * created first (0001_01_00_000000) and users is created after it
 * (0001_01_01_000000). Adding the constraint directly inside the
 * institutions migration causes a circular dependency and fails on a
 * fresh `php artisan migrate`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->foreign('verified_by', 'institutions_verified_by_foreign')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropForeign('institutions_verified_by_foreign');
        });
    }
};
