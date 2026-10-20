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
        Schema::create('alumni_post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumni_post_id')->constrained('alumni_posts')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('disk', 50)->default('public');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->string('original_name')->nullable();
            $table->string('caption')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_cover')->default(false);
            $table->timestamps();

            $table->index(['alumni_post_id', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumni_post_media');
    }
};
