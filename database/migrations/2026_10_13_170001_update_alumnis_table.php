<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alumnis', function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->after('rapports_donnees');
            $table->boolean('is_visible_directory')->default(true)->after('is_approved');
            $table->string('linkedin_url')->nullable()->after('is_visible_directory');
            $table->string('portfolio_url')->nullable()->after('linkedin_url');
            $table->text('bio')->nullable()->after('portfolio_url');

            // Contrainte d'unicité sur student_profile_id pour éviter les doublons par étudiant
            $table->unique('student_profile_id', 'alumnis_student_profile_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnis', function (Blueprint $table) {
            $table->dropUnique('alumnis_student_profile_id_unique');
            $table->dropColumn([
                'is_approved',
                'is_visible_directory',
                'linkedin_url',
                'portfolio_url',
                'bio',
            ]);
        });
    }
};
