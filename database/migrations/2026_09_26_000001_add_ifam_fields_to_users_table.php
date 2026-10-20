<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration IFAM Connect — Enrichissement de la table users.
 *
 * Ajoute les champs métier communs à tous les acteurs du système :
 * identity (first_name, last_name, gender, birth_date, phone),
 * statut compte (is_active, must_change_password, last_login_at)
 * et le matricule pour les étudiants (unique, nullable pour les autres rôles).
 *
 * Les champs spécifiques à chaque profil métier sont dans leurs propres tables
 * (student_profiles, teacher_profiles…).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Identité civile
            $table->string('first_name')->after('id');
            $table->string('last_name')->after('first_name');
            $table->enum('gender', ['M', 'F', 'autre'])->nullable()->after('last_name');
            $table->date('birth_date')->nullable()->after('gender');
            $table->string('phone', 20)->nullable()->after('birth_date');
            $table->string('photo_path')->nullable()->after('phone');

            // Matricule — généré automatiquement pour les étudiants
            $table->string('matricule', 30)->unique()->nullable()->after('photo_path');

            // Statut du compte
            $table->boolean('is_active')->default(true)->after('email_verified_at');
            $table->boolean('must_change_password')->default(true)->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('must_change_password');

            // Créé par (admin responsable de la création)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('last_login_at');

            // Suppression douce — on ne supprime jamais un utilisateur IFAM
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn([
                'first_name', 'last_name', 'gender', 'birth_date', 'phone',
                'photo_path', 'matricule', 'is_active', 'must_change_password',
                'last_login_at',
            ]);
        });
    }
};
