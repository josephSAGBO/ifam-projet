<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('parent_etudiant')) {
            Schema::create('parent_etudiant', function (Blueprint $table) {
                $table->id();
                $table->foreignId('parent_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->string('relationship', 50)->nullable();
                $table->timestamps();

                $table->unique(['parent_id', 'student_id']);
            });
        }

        // Migrate from parent_student if exists (only for existing users to respect FKs)
        if (Schema::hasTable('parent_student')) {
            $validUserIds = DB::table('users')->pluck('id')->all();
            $existing = DB::table('parent_student')->get();

            foreach ($existing as $row) {
                if (! in_array($row->parent_id, $validUserIds) || ! in_array($row->student_id, $validUserIds)) {
                    continue;
                }

                $exists = DB::table('parent_etudiant')
                    ->where('parent_id', $row->parent_id)
                    ->where('student_id', $row->student_id)
                    ->exists();

                if (! $exists) {
                    DB::table('parent_etudiant')->insert([
                        'parent_id' => $row->parent_id,
                        'student_id' => $row->student_id,
                        'relationship' => $row->relationship ?? null,
                        'created_at' => $row->created_at ?? now(),
                        'updated_at' => $row->updated_at ?? now(),
                    ]);
                }
            }
        }

        // Migrate from student_profiles.parent_id if it ever existed
        if (Schema::hasColumn('student_profiles', 'parent_id')) {
            $validUserIds = DB::table('users')->pluck('id')->all();
            $profiles = DB::table('student_profiles')->whereNotNull('parent_id')->get();
            foreach ($profiles as $sp) {
                if (! in_array($sp->parent_id, $validUserIds) || ! in_array($sp->user_id, $validUserIds)) {
                    continue;
                }

                $exists = DB::table('parent_etudiant')
                    ->where('parent_id', $sp->parent_id)
                    ->where('student_id', $sp->user_id)
                    ->exists();

                if (! $exists) {
                    DB::table('parent_etudiant')->insert([
                        'parent_id' => $sp->parent_id,
                        'student_id' => $sp->user_id,
                        'relationship' => 'Parent',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            Schema::table('student_profiles', function (Blueprint $table) {
                $table->dropColumn('parent_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_etudiant');
    }
};
