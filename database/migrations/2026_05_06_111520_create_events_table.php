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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->boolean('is_holiday')->default(false);
            $table->foreignId('event_type_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('audience', ['everyone', 'class', 'section'])->default('everyone');
            $table->date('date_from');
            $table->date('date_to')->nullable();
            $table->longText('description')->nullable();
            $table->boolean('show_website')->default(false);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
