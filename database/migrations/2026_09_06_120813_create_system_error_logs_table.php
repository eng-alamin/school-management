<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_error_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('institution_id')->nullable()
                ->constrained('institutions')->nullOnDelete();

            $table->foreignId('branch_id')->nullable()
                ->constrained('branches')->nullOnDelete();

            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->string('user_role', 100)->nullable();
            $table->string('panel', 50)->nullable();      // admin, ministry, teacher, student, branch, accountant, parent
            $table->string('component', 191)->nullable(); // Livewire component class name

            $table->string('exception_class', 191);
            $table->text('message');
            $table->string('file', 500)->nullable();
            $table->integer('line')->nullable();
            $table->longText('trace')->nullable();
            $table->json('context')->nullable();

            $table->string('url', 500)->nullable();
            $table->string('method', 10)->nullable();
            $table->string('ip', 45)->nullable();

            $table->enum('status', ['new', 'reviewed', 'resolved'])->default('new');

            $table->timestamps();

            $table->index(['institution_id', 'created_at'], 'sel_institution_created_idx');
            $table->index(['branch_id', 'created_at'], 'sel_branch_created_idx');
            $table->index(['status'], 'sel_status_idx');
            $table->index(['exception_class'], 'sel_exception_class_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_error_logs');
    }
};