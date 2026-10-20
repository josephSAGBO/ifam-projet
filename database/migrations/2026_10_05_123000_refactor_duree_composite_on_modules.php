<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Remplace duree_valeur + duree_unite par 4 champs composés :
 * duree_mois, duree_semaines, duree_heures, duree_minutes.
 * Les données existantes sont converties vers le champ correspondant.
 * 1 mois = 160h, 1 semaine = 40h, heure reste heure.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->unsignedSmallInteger('duree_mois')->nullable()->after('coefficient');
            $table->unsignedSmallInteger('duree_semaines')->nullable()->after('duree_mois');
            $table->unsignedSmallInteger('duree_heures')->nullable()->after('duree_semaines');
            $table->unsignedSmallInteger('duree_minutes')->nullable()->after('duree_heures');
        });

        // Migrer les données existantes depuis duree_valeur + duree_unite
        if (Schema::hasColumn('modules', 'duree_valeur') && Schema::hasColumn('modules', 'duree_unite')) {
            $modules = DB::table('modules')->whereNotNull('duree_valeur')->get(['id', 'duree_valeur', 'duree_unite']);

            foreach ($modules as $module) {
                $val = (int) $module->duree_valeur;
                $unite = $module->duree_unite;

                $updateData = match ($unite) {
                    'mois' => ['duree_mois' => $val],
                    'semaine' => ['duree_semaines' => $val],
                    'heure' => ['duree_heures' => $val],
                    'jour' => ['duree_heures' => $val * 8],
                    default => ['duree_heures' => $val],
                };

                DB::table('modules')->where('id', $module->id)->update($updateData);
            }
        }

        // Supprimer les anciennes colonnes
        Schema::table('modules', function (Blueprint $table) {
            if (Schema::hasColumn('modules', 'duree_valeur')) {
                $table->dropColumn('duree_valeur');
            }
            if (Schema::hasColumn('modules', 'duree_unite')) {
                $table->dropColumn('duree_unite');
            }
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->unsignedSmallInteger('duree_valeur')->nullable();
            $table->string('duree_unite', 10)->default('heure');
        });

        // Reconvertir depuis les champs composés (approximation)
        DB::table('modules')
            ->whereNotNull('duree_mois')
            ->orWhereNotNull('duree_semaines')
            ->orWhereNotNull('duree_heures')
            ->each(function ($m) {
                $heures = (($m->duree_mois ?? 0) * 160)
                    + (($m->duree_semaines ?? 0) * 40)
                    + ($m->duree_heures ?? 0);

                DB::table('modules')->where('id', $m->id)->update([
                    'duree_valeur' => max(1, $heures),
                    'duree_unite' => 'heure',
                ]);
            });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['duree_mois', 'duree_semaines', 'duree_heures', 'duree_minutes']);
        });
    }
};
