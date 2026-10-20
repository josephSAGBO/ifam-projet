<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'date')) {
                $table->date('date')->nullable()->after('color');
            }
            if (! Schema::hasColumn('courses', 'heure_debut')) {
                $table->time('heure_debut')->nullable()->after('date');
            }
            if (! Schema::hasColumn('courses', 'heure_fin')) {
                $table->time('heure_fin')->nullable()->after('heure_debut');
            }
            if (! Schema::hasColumn('courses', 'salle_id')) {
                $table->foreignId('salle_id')->nullable()->after('heure_fin')->constrained('rooms')->nullOnDelete();
            }
            if (! Schema::hasColumn('courses', 'enseignant_id')) {
                $table->foreignId('enseignant_id')->nullable()->after('salle_id')->constrained('teacher_profiles')->nullOnDelete();
            }
        });

        // Copy teacher_profile_id to enseignant_id if exists
        if (Schema::hasColumn('courses', 'teacher_profile_id')) {
            DB::table('courses')->whereNotNull('teacher_profile_id')->whereNull('enseignant_id')->update([
                'enseignant_id' => DB::raw('teacher_profile_id'),
            ]);
        }

        if (! Schema::hasTable('documents_cours')) {
            Schema::create('documents_cours', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cours_id')->constrained('courses')->cascadeOnDelete();
                $table->string('title');
                $table->string('file_path');
                $table->string('original_name');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('documents_cours');

        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('salle_id');
            $table->dropConstrainedForeignId('enseignant_id');
            $table->dropColumn(['date', 'heure_debut', 'heure_fin']);
        });
    }
};
