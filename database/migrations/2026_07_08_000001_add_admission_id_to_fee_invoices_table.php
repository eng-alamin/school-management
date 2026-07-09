<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * fee_invoices table e nullable admission_id FK add kora hocche.
     * Nullable rakha hoyeche jate existing invoice rows (student-based)
     * kono bhabe affect na hoy — full backward compatible.
     */
    public function up(): void
    {
        Schema::table('fee_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('fee_invoices', 'admission_id')) {
                $table->foreignId('admission_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('admissions')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('fee_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('fee_invoices', 'admission_id')) {
                $table->dropForeign(['admission_id']);
                $table->dropColumn('admission_id');
            }
        });
    }
};