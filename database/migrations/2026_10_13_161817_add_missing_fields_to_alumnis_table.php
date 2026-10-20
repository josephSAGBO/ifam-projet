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
        Schema::table('alumnis', function (Blueprint $table) {
            // Année de promotion (diplôme)
            $table->unsignedSmallInteger('promotion_year')->nullable()->after('date_diplome');
            // Poste actuel (plus lisible que poste_actuel / statut_actuel)
            $table->string('current_position')->nullable()->after('promotion_year');
            // Entreprise actuelle (alias lisible)
            $table->string('company')->nullable()->after('current_position');
            // Ville de résidence
            $table->string('city')->nullable()->after('company');
            // Contact public email autorisé
            $table->boolean('allow_public_email')->default(false)->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnis', function (Blueprint $table) {
            $table->dropColumn([
                'promotion_year',
                'current_position',
                'company',
                'city',
                'allow_public_email',
            ]);
        });
    }
};
