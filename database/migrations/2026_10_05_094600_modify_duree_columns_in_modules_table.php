<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('modules', 'duree')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->renameColumn('duree', 'duree_valeur');
            });
        } else {
            Schema::table('modules', function (Blueprint $table) {
                $table->integer('duree_valeur')->nullable();
            });
        }

        Schema::table('modules', function (Blueprint $table) {
            $table->string('duree_unite', 10)->default('heure');
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('duree_unite');
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->renameColumn('duree_valeur', 'duree');
        });
    }
};
