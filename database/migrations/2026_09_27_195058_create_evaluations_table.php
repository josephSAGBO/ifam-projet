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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('title'); // e.g. "Contrôle Continu 1", "Examen Final"
            $table->enum('type', ['CC', 'Exam', 'Projet', 'TP'])->default('Exam');
            $table->decimal('coefficient', 5, 2)->default(1.0);
            $table->integer('max_score')->default(20);
            $table->date('evaluation_date')->nullable();
            $table->boolean('is_published')->default(false); // Can grades be seen by students?
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
