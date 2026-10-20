<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            // Propriétaire polymorphe : StudentProfile, TeacherProfile, ParentProfile, etc.
            $table->morphs('documentable');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->enum('type', [
                'identity',       // CNI, passeport
                'diploma',        // diplôme
                'transcript',     // relevé de notes
                'enrollment',     // certificat d'inscription
                'medical',        // certificat médical
                'contract',       // contrat
                'invoice',        // facture
                'other',          // autre
            ])->default('other');

            $table->string('file_path');       // chemin relatif dans storage
            $table->string('original_name');   // nom original du fichier
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable(); // en octets
            $table->boolean('is_verified')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
