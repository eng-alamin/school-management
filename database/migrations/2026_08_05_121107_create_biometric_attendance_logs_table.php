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
        Schema::create('biometric_attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('biometric_device_id')->constrained('biometric_devices')->cascadeOnDelete();
            $table->string('device_user_id');
            $table->nullableMorphs('attendable'); // resolved after mapping lookup
            $table->dateTime('punch_time');
            $table->unsignedTinyInteger('verify_mode')->nullable(); // 0 pass,1 fp,2 card,15 face
            $table->unsignedTinyInteger('in_out_mode')->nullable(); // 0 in,1 out,2 break-out,3 break-in etc.
            $table->unsignedTinyInteger('work_code')->nullable();
            $table->text('raw_payload')->nullable();
            $table->boolean('processed')->default(false);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'processed'], 'bio_log_inst_processed_idx');
            $table->index(['biometric_device_id', 'device_user_id', 'punch_time'], 'bio_log_device_user_time_idx');
            // duplicate punch prevent: same device, same user, same exact timestamp আসতে পারে না দুইবার
            $table->unique(
                ['biometric_device_id', 'device_user_id', 'punch_time'],
                'bio_log_unique_punch'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_attendance_logs');
    }
};
