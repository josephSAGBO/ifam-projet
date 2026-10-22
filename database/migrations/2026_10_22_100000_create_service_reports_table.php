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
        Schema::create('service_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_profile_id')->constrained('service_profiles')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('auteur_id')->constrained('users')->cascadeOnDelete();
            $table->string('type'); // 'hebdomadaire', 'mensuel', 'ponctuel'
            $table->date('periode_debut');
            $table->date('periode_fin');
            $table->string('titre');
            $table->text('resume');
            $table->text('activites');
            $table->text('difficultes')->nullable();
            $table->text('besoins')->nullable();
            $table->json('indicateurs')->nullable();
            $table->string('statut')->default('brouillon'); // 'brouillon', 'envoye', 'lu', 'retourne'
            $table->timestamp('envoye_le')->nullable();
            $table->timestamp('lu_le')->nullable();
            $table->foreignId('lu_par')->nullable()->constrained('users')->nullOnDelete();
            $table->text('commentaire_direction')->nullable();
            $table->timestamps();

            $table->unique(['service_profile_id', 'site_id', 'type', 'periode_debut'], 'service_reports_periodic_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_reports');
    }
};
