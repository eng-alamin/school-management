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
        Schema::create('biometric_device_user_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('biometric_device_id')->constrained('biometric_devices')->cascadeOnDelete();
            $table->string('device_user_id'); // ID device-এ enroll করার সময় দেওয়া হয়েছে
            $table->string('card_number')->nullable(); // Card number associated with the device user
            $table->string('attendable_type'); // App\Models\Student / App\Models\Employee
            $table->unsignedBigInteger('attendable_id'); // integer id, string code না
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['biometric_device_id', 'device_user_id', 'deleted_at'],
                'bio_map_device_user_unique'
            );
            $table->index(['attendable_type', 'attendable_id'], 'bio_map_attendable_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_device_user_mappings');
    }
};
