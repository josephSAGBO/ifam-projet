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
        Schema::create('partnerships', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // Nom de la structure / entreprise / institution
            $table->string('prenom')->nullable(); // Prénom de l'interlocuteur / contact principal
            $table->string('telephone')->nullable(); // Numéro de téléphone
            $table->string('email')->nullable(); // Email
            $table->string('type_partenariat'); // Type de partenariat (Stage, Emploi, Matériel, Financier, Parrainage, Formations, Autre)
            $table->string('duree')->nullable(); // Durée du partenariat
            $table->string('secteur_intervention')->nullable(); // Secteur d'activité / d'intervention
            $table->text('ce_qu_ils_offrent')->nullable(); // Ce qu'ils nous offrent
            $table->text('ce_qu_on_offre')->nullable(); // Ce qu'on leur offre
            $table->string('personne_ressource_interne')->nullable(); // Personne ressource dans la boîte (IFAM)
            $table->text('suivi')->nullable(); // Suivi / Remarques
            $table->date('date_dernier_contact')->nullable(); // Date du dernier contact ou dernier appel
            $table->string('statut')->default('actif'); // actif, en_discussion, inactif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partnerships');
    }
};
