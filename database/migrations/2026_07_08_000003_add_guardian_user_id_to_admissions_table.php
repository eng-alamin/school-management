<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * IMPORTANT: Eta age-er "add_guardian_id_to_admissions_table" migration-er
     * PORIBORTE use hobe — jodi shei migration age theke run kore fela hoy,
     * tahole age seta rollback (php artisan migrate:rollback) kore tarpor
     * ei notun ta migrate korte hobe.
     *
     * Karon: ekjon Guardian-er 5ta sontan 5ta different Institution-e
     * porte pare — tai guardian_id (single-institution-scoped Guardian
     * row) direct link kora thik na. Er poriborte guardian_user_id
     * (users.id) link kora hocche, jeta guardian-er GLOBAL login
     * identity — ei ekta id diye AdmissionService prottek Institution-e
     * proyojon mote alada Guardian row toiri/reuse korte parbe, kintu
     * login (User account) sob shomoy ekta-i thakbe.
     */
    public function up(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->foreignId('guardian_user_id')
                ->nullable()
                ->after('institution_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('admissions', function (Blueprint $table) {
            $table->dropForeign(['guardian_user_id']);
            $table->dropColumn('guardian_user_id');
        });
    }
};