<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            if (! Schema::hasColumn('evaluations', 'formation_id')) {
                $table->foreignId('formation_id')->nullable()->after('module_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('evaluations', 'assigned_teacher_profile_id')) {
                $table->foreignId('assigned_teacher_profile_id')->nullable()->after('created_by')->constrained('teacher_profiles')->nullOnDelete();
            }
        });

        $evaluations = DB::table('evaluations')->whereNotNull('cours_id')->get(['id', 'cours_id']);
        foreach ($evaluations as $evaluation) {
            $course = DB::table('courses')->where('id', $evaluation->cours_id)->first(['formation_id', 'enseignant_id', 'teacher_profile_id']);

            $updates = [];
            if ($course && ! DB::table('evaluations')->where('id', $evaluation->id)->whereNotNull('formation_id')->exists()) {
                $updates['formation_id'] = $course->formation_id;
            }
            if ($course && ! DB::table('evaluations')->where('id', $evaluation->id)->whereNotNull('assigned_teacher_profile_id')->exists()) {
                $updates['assigned_teacher_profile_id'] = $course->enseignant_id ?? $course->teacher_profile_id;
            }

            if ($updates !== []) {
                DB::table('evaluations')->where('id', $evaluation->id)->update($updates);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('formation_id');
            $table->dropConstrainedForeignId('assigned_teacher_profile_id');
        });
    }
};
