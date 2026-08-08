<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('division')->nullable()->after('city');
            $table->string('district')->nullable()->after('division');
        });

        // Composite index for Ministry filter queries (division/district-wise reports)
        Schema::table('institutions', function (Blueprint $table) {
            $table->index(['division', 'district']);
        });
    }

    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropIndex(['division', 'district']);
            $table->dropColumn(['division', 'district']);
        });
    }
};