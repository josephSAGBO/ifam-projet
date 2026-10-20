<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        // Add coefficient and duree on all drivers (no FK involved)
        Schema::table('modules', function (Blueprint $table) {
            if (! Schema::hasColumn('modules', 'coefficient')) {
                $table->decimal('coefficient', 5, 2)->default(1.00);
            }
            if (! Schema::hasColumn('modules', 'duree')) {
                $table->integer('duree')->nullable();
            }
        });

        // SQLite cannot handle the add-FK-then-drop-FK pattern.
        // formation_id is removed in a later migration anyway, so skip it on SQLite.
        if ($isSqlite) {
            return;
        }

        // 1. Add formation_id nullable first
        Schema::table('modules', function (Blueprint $table) {
            if (! Schema::hasColumn('modules', 'formation_id')) {
                $table->unsignedBigInteger('formation_id')->nullable()->after('id');
            }
        });

        // 2. Populate formation_id for existing modules from courses or fallback to a default formation
        $modules = DB::table('modules')->get();
        $defaultFormationId = DB::table('formations')->value('id');

        foreach ($modules as $m) {
            $formationId = null;
            if (Schema::hasTable('courses')) {
                $course = DB::table('courses')->where('module_id', $m->id)->whereNotNull('formation_id')->first();
                if ($course) {
                    $formationId = $course->formation_id;
                }
            }

            if (! $formationId) {
                $formationId = $defaultFormationId;
            }

            $updateData = ['formation_id' => $formationId];
            if (isset($m->credits) && $m->credits > 0) {
                $updateData['coefficient'] = $m->credits;
            }
            DB::table('modules')->where('id', $m->id)->update($updateData);
        }

        // 3. Ensure no module has null formation_id
        if ($defaultFormationId) {
            DB::table('modules')->whereNull('formation_id')->update(['formation_id' => $defaultFormationId]);
        }

        // 4. Add constraint & make non-nullable, drop credits
        Schema::table('modules', function (Blueprint $table) {
            $table->unsignedBigInteger('formation_id')->nullable(false)->change();
            $table->foreign('formation_id')->references('id')->on('formations')->cascadeOnDelete();

            if (Schema::hasColumn('modules', 'credits')) {
                $table->dropColumn('credits');
            }
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('modules', function (Blueprint $table) {
            $table->integer('credits')->default(1);
            $table->dropForeign(['formation_id']);
            $table->dropColumn(['formation_id', 'coefficient', 'duree']);
        });
    }
};
