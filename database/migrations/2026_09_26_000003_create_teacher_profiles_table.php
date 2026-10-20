<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration IFAM Connect — Table des profils enseignants.
 *
 * Contient les informations métier spécifiques à l'enseignant/formateur :
 * spécialité, type de contrat, numéro de CV/CIN, etc.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();

            // Informations professionnelles
            $table->string('speciality')->nullable(); // Ex: Modélisme, Couture industrielle…
            $table->enum('teacher_type', ['permanent', 'vacataire', 'maitre_artisan'])->default('permanent');
            $table->string('qualification')->nullable(); // Diplôme / certification
            $table->date('hired_at')->nullable();

            // CIN / Numéro identifiant professionnel
            $table->string('id_number')->nullable();

            // Notes internes
            $table->text('admin_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_profiles');
    }
};
