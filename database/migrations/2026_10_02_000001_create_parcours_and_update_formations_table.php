<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('parcours')) {
            Schema::create('parcours', function (Blueprint $table) {
                $table->id();
                $table->string('nom');
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('formations', function (Blueprint $table) {
            if (! Schema::hasColumn('formations', 'prix')) {
                $table->decimal('prix', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('formations', 'parcours_id')) {
                $table->foreignId('parcours_id')->nullable()->constrained('parcours')->nullOnDelete();
            }
            if (! Schema::hasColumn('formations', 'ordre')) {
                $table->integer('ordre')->nullable();
            }
            if (Schema::hasColumn('formations', 'level')) {
                $table->dropColumn('level');
            }
            if (Schema::hasColumn('formations', 'annee')) {
                $table->dropColumn('annee');
            }
        });

        // Convert existing multi-year formations if present
        $existingParcours = DB::table('parcours')->where('nom', 'Stylisme Modélisme')->first();
        $parcoursId = $existingParcours ? $existingParcours->id : DB::table('parcours')->insertGetId([
            'nom' => 'Stylisme Modélisme',
            'description' => 'Parcours pluriannuel Stylisme & Modélisme',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('formations')->where('id', 3)->update([
            'prix' => 400000.00,
            'parcours_id' => null,
            'ordre' => null,
        ]);

        DB::table('formations')->where('id', 4)->update([
            'prix' => 450000.00,
            'parcours_id' => $parcoursId,
            'ordre' => 1,
        ]);

        DB::table('formations')->where('id', 5)->update([
            'prix' => 450000.00,
            'parcours_id' => $parcoursId,
            'ordre' => 2,
        ]);
    }

    public function down(): void
    {
        Schema::table('formations', function (Blueprint $table) {
            $table->string('level')->nullable();
            $table->dropConstrainedForeignId('parcours_id');
            $table->dropColumn(['prix', 'ordre']);
        });

        Schema::dropIfExists('parcours');
    }
};
