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
        Schema::create('user_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('level')->default('agent'); // 'agent' | 'responsable'
            $table->boolean('is_department_head')->default(false);
            $table->string('department_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'service_profile_id', 'site_id', 'level'], 'user_service_site_level_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_assignments');
    }
};
