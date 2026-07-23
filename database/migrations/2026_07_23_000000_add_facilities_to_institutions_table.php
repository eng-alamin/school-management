<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            // JSON column to store facility flags, e.g. {"library": true, "transport": false}
            // Placed after 'weekends' to keep related settings-type columns together.
            $table->json('facilities')->nullable()->after('weekends');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn('facilities');
        });
    }
};
