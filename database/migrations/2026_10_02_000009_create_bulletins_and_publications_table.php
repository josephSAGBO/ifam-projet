<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bulletin_publications')) {
            Schema::create('bulletin_publications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('formation_id')->constrained('formations')->cascadeOnDelete();
                $table->foreignId('academic_session_id')->constrained('academic_sessions')->cascadeOnDelete();
                $table->foreignId('student_profile_id')->nullable()->constrained('student_profiles')->cascadeOnDelete();
                $table->boolean('is_published')->default(true);
                $table->timestamp('published_at')->nullable();
                $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['formation_id', 'academic_session_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bulletin_publications');
    }
};
