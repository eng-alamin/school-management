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
        Schema::create('id_card_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->default('general'); // general, student, employee
            $table->string('background_color')->default('#ffffff');
            $table->string('text_color')->default('#000000');
            $table->string('accent_color')->default('#007bff');
            $table->string('logo_path')->nullable();
            $table->string('background_image')->nullable();
            $table->text('header_text')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('card_width')->default('85.6mm');
            $table->string('card_height')->default('54mm');
            $table->json('fields')->nullable(); // custom fields
            $table->boolean('show_photo')->default(true);
            $table->boolean('show_barcode')->default(false);
            $table->boolean('show_qrcode')->default(false);
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
        Schema::dropIfExists('id_card_templates');
    }
};
