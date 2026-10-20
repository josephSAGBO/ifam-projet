<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('enseignant_module')) {
            Schema::create('enseignant_module', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_profile_id')->constrained('teacher_profiles')->cascadeOnDelete();
                $table->foreignId('module_id')->constrained('modules')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['teacher_profile_id', 'module_id']);
            });
        }

        // Attach existing teachers to modules based on existing courses
        if (Schema::hasTable('courses')) {
            $courses = DB::table('courses')
                ->whereNotNull('teacher_profile_id')
                ->whereNotNull('module_id')
                ->select('teacher_profile_id', 'module_id')
                ->distinct()
                ->get();

            foreach ($courses as $c) {
                $exists = DB::table('enseignant_module')
                    ->where('teacher_profile_id', $c->teacher_profile_id)
                    ->where('module_id', $c->module_id)
                    ->exists();

                if (! $exists) {
                    DB::table('enseignant_module')->insert([
                        'teacher_profile_id' => $c->teacher_profile_id,
                        'module_id' => $c->module_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        Schema::table('teacher_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('teacher_profiles', 'speciality')) {
                $table->dropColumn('speciality');
            }
        });
    }

    public function down(): void
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->string('speciality')->nullable();
        });

        Schema::dropIfExists('enseignant_module');
    }
};
