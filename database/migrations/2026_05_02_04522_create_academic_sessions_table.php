<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('academic_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(true);
            $table->boolean('is_status')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['institution_id', 'branch_id', 'is_current', 'deleted_at'], 'academic_sessions_ins_bra_cur_del_idx');
            $table->unique(['institution_id', 'branch_id', 'name', 'deleted_at'], 'academic_sessions_ins_bra_name_del_unique');
        });

        DB::statement("
            ALTER TABLE academic_sessions
            ADD COLUMN current_session_key VARCHAR(191)
            GENERATED ALWAYS AS (
                CASE
                    WHEN is_current = 1 AND deleted_at IS NULL
                    THEN CONCAT(institution_id, '-', COALESCE(branch_id, 0))
                    ELSE NULL
                END
            ) VIRTUAL
        ");

        DB::statement("
            CREATE UNIQUE INDEX academic_sessions_one_current_per_branch
            ON academic_sessions (current_session_key)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_sessions');
    }
};