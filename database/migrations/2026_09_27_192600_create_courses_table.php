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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('formation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();

            // Teacher can be nullable if a course is created but not yet assigned
            $table->foreignId('teacher_profile_id')->nullable()->constrained()->nullOnDelete();

            // Course Details
            $table->string('name')->nullable(); // In case we want a custom name, otherwise use module's name
            $table->string('color')->default('#e40066'); // To display on schedule

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
