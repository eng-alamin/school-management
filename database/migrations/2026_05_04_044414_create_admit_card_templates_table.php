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
        Schema::create('admit_card_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('exam_type')->nullable(); // annual, half-yearly, quarterly, etc.
            $table->string('background_color')->default('#ffffff');
            $table->string('text_color')->default('#000000');
            $table->string('accent_color')->default('#dc3545');
            $table->string('logo_path')->nullable();
            $table->text('header_text')->nullable();
            $table->text('instructions')->nullable();
            $table->text('footer_text')->nullable();
            $table->boolean('show_photo')->default(true);
            $table->boolean('show_signature')->default(true);
            $table->boolean('show_barcode')->default(false);
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
        Schema::dropIfExists('admit_card_templates');
    }
};
