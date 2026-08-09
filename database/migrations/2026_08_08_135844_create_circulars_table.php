<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('circulars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description');
            $table->string('attachment')->nullable();
            $table->string('attachment_name')->nullable();
            $table->enum('audience', ['all', 'division', 'district', 'institution'])->default('all');
            $table->string('division')->nullable();
            $table->string('district')->nullable();
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->date('published_at');
            $table->date('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('circular_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('circular_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('read_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(['circular_id', 'institution_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('circular_reads');
        Schema::dropIfExists('circulars');
    }
};