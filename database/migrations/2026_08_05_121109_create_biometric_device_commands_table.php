<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biometric_device_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            
            $table->foreignId('biometric_device_id')->constrained('biometric_devices')->cascadeOnDelete();

            // device-কে যেই command_id পাঠানো হয় (ADMS format: C:<command_id>:DATA ...)
            $table->string('command_id')->unique();
            $table->text('command_text');

            $table->string('card_number')->nullable();

            $table->enum('status', ['pending', 'sent', 'confirmed', 'failed'])->default('pending')->index();
            $table->text('response')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();

            $table->timestamps();

            $table->index(['biometric_device_id', 'status'], 'bio_cmd_device_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_device_commands');
    }
};
