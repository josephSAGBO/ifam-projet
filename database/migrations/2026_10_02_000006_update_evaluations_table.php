<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add new columns if they do not exist yet
        Schema::table('evaluations', function (Blueprint $table) {
            if (! Schema::hasColumn('evaluations', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('id')->constrained('users')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('evaluations', 'cours_id')) {
                $table->foreignId('cours_id')->nullable()->after('created_by')->constrained('courses')->nullOnDelete();
            }
            if (! Schema::hasColumn('evaluations', 'module_id')) {
                $table->foreignId('module_id')->nullable()->after('cours_id')->constrained('modules')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('evaluations', 'session_id')) {
                $table->foreignId('session_id')->nullable()->after('module_id')->constrained('academic_sessions')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('evaluations', 'titre')) {
                $table->string('titre')->nullable()->after('session_id');
            }
            if (! Schema::hasColumn('evaluations', 'date')) {
                $table->date('date')->nullable()->after('titre');
            }
            if (! Schema::hasColumn('evaluations', 'statut')) {
                $table->enum('statut', ['brouillon', 'ouverte', 'cloturee'])->default('brouillon')->after('date');
            }
        });

        // 2. Transfer existing data if old columns still exist
        $firstAdminId = DB::table('users')->where('email', 'like', '%admin%')->value('id') ?? DB::table('users')->value('id');
        $evaluations = DB::table('evaluations')->get();

        foreach ($evaluations as $e) {
            $course = null;
            $existingCourseId = $e->course_id ?? $e->cours_id ?? null;
            if ($existingCourseId) {
                $course = DB::table('courses')->where('id', $existingCourseId)->first();
            }

            $moduleId = $e->module_id ?? ($course ? $course->module_id : DB::table('modules')->value('id'));
            $sessionId = $e->session_id ?? ($course ? $course->academic_session_id : DB::table('academic_sessions')->value('id'));

            DB::table('evaluations')->where('id', $e->id)->update([
                'created_by' => $e->created_by ?? $firstAdminId,
                'cours_id' => $existingCourseId,
                'module_id' => $moduleId,
                'session_id' => $sessionId,
                'titre' => $e->titre ?? ($e->title ?? 'Évaluation #'.$e->id),
                'date' => $e->date ?? ($e->evaluation_date ?? now()->toDateString()),
                'statut' => $e->statut ?? ((isset($e->is_published) && $e->is_published) ? 'ouverte' : 'brouillon'),
            ]);
        }

        // 3. Drop old columns if they still exist
        Schema::table('evaluations', function (Blueprint $table) {
            if (Schema::hasColumn('evaluations', 'course_id')) {
                try {
                    $table->dropForeign(['course_id']);
                } catch (Throwable $e) {
                }
                $table->dropColumn('course_id');
            }
            if (Schema::hasColumn('evaluations', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('evaluations', 'evaluation_date')) {
                $table->dropColumn('evaluation_date');
            }
            if (Schema::hasColumn('evaluations', 'coefficient')) {
                $table->dropColumn('coefficient');
            }
            if (Schema::hasColumn('evaluations', 'max_score')) {
                $table->dropColumn('max_score');
            }
            if (Schema::hasColumn('evaluations', 'is_published')) {
                $table->dropColumn('is_published');
            }
        });

        // 4. Update type column to enum('devoir', 'controle_continu') safely without 1265 truncation warning
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `evaluations` MODIFY `type` VARCHAR(50) NOT NULL DEFAULT 'devoir'");
            DB::statement("UPDATE `evaluations` SET `type` = IF(`cours_id` IS NOT NULL, 'controle_continu', 'devoir')");
            DB::statement("ALTER TABLE `evaluations` MODIFY `type` ENUM('devoir', 'controle_continu') NOT NULL DEFAULT 'devoir'");
        } else {
            DB::table('evaluations')->whereNotNull('cours_id')->update(['type' => 'controle_continu']);
            DB::table('evaluations')->whereNull('cours_id')->update(['type' => 'devoir']);
        }
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->constrained('courses')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->decimal('coefficient', 5, 2)->default(1.0);
            $table->integer('max_score')->default(20);
            $table->date('evaluation_date')->nullable();
            $table->boolean('is_published')->default(false);

            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('cours_id');
            $table->dropConstrainedForeignId('module_id');
            $table->dropConstrainedForeignId('session_id');
            $table->dropColumn(['titre', 'date', 'statut']);
        });
    }
};
