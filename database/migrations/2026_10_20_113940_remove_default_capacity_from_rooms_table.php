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
        Schema::table('rooms', function (Blueprint $table) {
            $table->integer('capacity')->nullable()->default(null)->change();
        });

        // Régularisation des salles historiques :
        // 1. Shenghor : capacité réelle inconnue => NULL (pas de valeur arbitraire 30)
        DB::table('rooms')
            ->where('name', 'Shenghor')
            ->where('capacity', 30)
            ->update(['capacity' => null]);

        // 2. Salles avec capacités réelles documentées dans leur description historique
        DB::table('rooms')
            ->where('name', 'Salle Info A1')
            ->where('capacity', 30)
            ->update([
                'capacity' => 35,
                'type' => 'Laboratoire',
            ]);

        DB::table('rooms')
            ->where('name', 'Amphithéâtre Houdégbé')
            ->where('capacity', 30)
            ->update([
                'capacity' => 120,
                'type' => 'Théorie',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->integer('capacity')->nullable()->default(30)->change();
        });
    }
};
