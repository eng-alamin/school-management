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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->onDelete('cascade');
            $table->morphs('notifiable'); // notifiable_id + notifiable_type
            $table->string('type', 100);             // e.g. fee_due, attendance, admission
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();        // extra payload: icon, url, meta
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->timestamp('read_at')->nullable(); // null = unread
            $table->timestamps();

            $table->index(['institution_id', 'notifiable_id', 'notifiable_type']);
            $table->index(['institution_id', 'read_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
