<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_sites', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('nom');
            $table->string('type');                          // Entreprise, ONG, Institution publique, etc.
            $table->string('secteur_activite');

            // Description
            $table->text('description')->nullable();

            // Localisation
            $table->string('pays')->default('Bénin');
            $table->string('departement');
            $table->string('commune');
            $table->string('quartier')->nullable();
            $table->string('adresse');
            $table->string('repere')->nullable();

            // Contact principal
            $table->string('contact_nom');
            $table->string('contact_fonction')->nullable();
            $table->string('telephone_principal');
            $table->string('telephone_secondaire')->nullable();
            $table->string('email')->nullable();
            $table->string('site_web')->nullable();

            // Capacité & statut
            $table->unsignedSmallInteger('capacite_max')->nullable();
            $table->enum('statut', ['actif', 'suspendu', 'archive'])->default('actif');

            // Rattachement au site IFAM
            $table->foreignId('site_id')
                ->constrained('sites')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_sites');
    }
};
