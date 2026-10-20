<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration IFAM Connect — Table des profils étudiants.
 *
 * Contient les informations spécifiques au profil "Étudiant" :
 * numéro d'acte de naissance, lieu de naissance, nationalité, adresse,
 * niveau d'entrée, informations du tuteur légal d'urgence, etc.
 *
 * Relation : 1 user → 0 ou 1 student_profile (selon le rôle).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();

            // Naissance et origine
            $table->string('birth_place')->nullable();
            $table->string('nationality')->default('Béninoise');
            $table->string('birth_certificate_number')->nullable();

            // Adresse
            $table->string('address')->nullable();
            $table->string('city')->nullable();

            // Tuteur légal / contact d'urgence
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->string('guardian_relation')->nullable(); // père, mère, tuteur…

            // Informations académiques d'entrée
            $table->string('previous_school')->nullable();
            $table->string('previous_diploma')->nullable();
            $table->year('entry_year')->nullable();

            // Notes internes (admin only)
            $table->text('admin_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
