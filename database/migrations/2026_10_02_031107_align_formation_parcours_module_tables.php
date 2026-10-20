<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Formation : Supprime niveau et description
        Schema::table('formations', function (Blueprint $table) {
            if (Schema::hasColumn('formations', 'niveau')) {
                $table->dropColumn('niveau');
            }
            if (Schema::hasColumn('formations', 'description')) {
                $table->dropColumn('description');
            }
            // Add parcours_id if not exists
            if (! Schema::hasColumn('formations', 'parcours_id')) {
                $table->foreignId('parcours_id')->nullable()->constrained('parcours')->nullOnDelete();
            }
        });

        // 2. Parcours : Simple nomenclature (id, name, created_at, updated_at). Drop others.
        Schema::table('parcours', function (Blueprint $table) {
            if (Schema::hasColumn('parcours', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('parcours', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('parcours', 'formation_id')) {
                $table->dropColumn('formation_id');
            }
        });

        // 3. Module : Retire formation_id
        // On SQLite, formation_id was never added (migration 000003 is skipped), so this is a no-op.
        // On MySQL, we drop the FK first then the column.
        if (Schema::hasColumn('modules', 'formation_id')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropForeign(['formation_id']);
                $table->dropColumn('formation_id');
            });
        }

        // 4. Pivot formation_module
        if (! Schema::hasTable('formation_module')) {
            Schema::create('formation_module', function (Blueprint $table) {
                $table->id();
                $table->foreignId('formation_id')->constrained()->cascadeOnDelete();
                $table->foreignId('module_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // 5. Supprime ParcoursModule
        if (Schema::hasTable('parcours_module')) {
            Schema::dropIfExists('parcours_module');
        }
        if (Schema::hasTable('module_parcours')) {
            Schema::dropIfExists('module_parcours');
        }
    }

    public function down()
    {
        // One-way migration based on instructions
    }
};
