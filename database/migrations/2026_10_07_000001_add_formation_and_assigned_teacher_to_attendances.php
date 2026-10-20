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
        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'formation_id')) {
                $table->foreignId('formation_id')->nullable()->after('course_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('attendances', 'assigned_teacher_profile_id')) {
                $table->foreignId('assigned_teacher_profile_id')->nullable()->after('student_profile_id')->constrained('teacher_profiles')->nullOnDelete();
            }
        });

        $attendances = DB::table('attendances')
            ->join('courses', 'courses.id', '=', 'attendances.course_id')
            ->select('attendances.id', 'courses.formation_id as course_formation_id', 'courses.enseignant_id', 'courses.teacher_profile_id')
            ->get();

        foreach ($attendances as $attendance) {
            $updates = [];

            if (is_null(DB::table('attendances')->where('id', $attendance->id)->value('formation_id')) && ! empty($attendance->course_formation_id)) {
                $updates['formation_id'] = $attendance->course_formation_id;
            }

            if (is_null(DB::table('attendances')->where('id', $attendance->id)->value('assigned_teacher_profile_id')) && ! empty($attendance->enseignant_id ?? $attendance->teacher_profile_id)) {
                $updates['assigned_teacher_profile_id'] = $attendance->enseignant_id ?? $attendance->teacher_profile_id;
            }

            if ($updates !== []) {
                DB::table('attendances')->where('id', $attendance->id)->update($updates);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'formation_id')) {
                $table->dropConstrainedForeignId('formation_id');
            }
            if (Schema::hasColumn('attendances', 'assigned_teacher_profile_id')) {
                $table->dropConstrainedForeignId('assigned_teacher_profile_id');
            }
        });
    }
};
