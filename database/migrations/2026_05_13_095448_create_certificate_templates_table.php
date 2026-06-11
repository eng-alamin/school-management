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
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('certificate_name');
            $table->enum('applicable_user', ['student', 'employee']);
            $table->enum('page_layout', [
                'a4_portrait',
                'a4_landscape',
                'a5_portrait',
                'a5_landscape',
            ])->default('a4_portrait');
            $table->enum('qr_code_text', [
                'register_no',
                'roll_no',
                'name',
                'email',
                'mobile',
            ])->default('register_no');
            $table->enum('photo_style', ['square', 'circle'])->default('square');
            $table->unsignedInteger('photo_size')->default(100); // in px
            $table->unsignedInteger('margin_top')->default(80);
            $table->unsignedInteger('margin_right')->default(80);
            $table->unsignedInteger('margin_bottom')->default(80);
            $table->unsignedInteger('margin_left')->default(80);
            $table->string('signature_image')->nullable();
            $table->string('logo_image')->nullable();
            $table->string('background_image')->nullable();
            $table->longText('certificate_content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
