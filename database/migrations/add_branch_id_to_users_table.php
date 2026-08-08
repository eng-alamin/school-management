<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->nullable() // null = admin (role) viewing all branches, or not yet assigned
                ->after('institution_id')
                ->constrained('branches')
                ->nullOnDelete();

            $table->index(['branch_id', 'role'], 'users_branch_role_idx');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_branch_role_idx');
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};