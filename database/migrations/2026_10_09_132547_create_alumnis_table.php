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
        Schema::create('alumnis', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->string('photo_path')->nullable();
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->date('date_diplome')->nullable(); // Date d'obtention du diplôme
            $table->foreignId('formation_id')->nullable()->constrained('formations')->nullOnDelete(); // Formation suivie à l'IFAM
            $table->foreignId('student_profile_id')->nullable()->constrained('student_profiles')->nullOnDelete(); // Ancien profil étudiant si existant
            $table->string('lieu_residence')->nullable(); // Ville / Pays de résidence
            $table->string('statut_actuel')->nullable(); // En emploi, Entrepreneur, Études supérieures, Recherche d'emploi
            $table->string('entreprise_actuelle')->nullable(); // Entreprise / Employeur / Société
            $table->string('poste_actuel')->nullable(); // Intitulé du poste / Fonction
            $table->text('rapports_donnees')->nullable(); // Rapports, notes et nouvelles des diplômés
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alumnis');
    }
};
