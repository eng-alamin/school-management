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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->string('subject');
            $table->longText('body');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_important')->default(false);
            $table->boolean('is_trashed_by_sender')->default(false);
            $table->boolean('is_trashed_by_receiver')->default(false);
            $table->boolean('is_deleted_by_sender')->default(false);
            $table->boolean('is_deleted_by_receiver')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'receiver_id', 'is_trashed_by_receiver', 'is_deleted_by_receiver'], 'messages_inbox_lookup_idx');
            $table->index(['institution_id', 'sender_id', 'is_trashed_by_sender', 'is_deleted_by_sender'], 'messages_sent_lookup_idx');
            $table->index(['receiver_id', 'is_read'], 'messages_unread_lookup_idx');
            $table->index(['receiver_id', 'is_important'], 'messages_important_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
