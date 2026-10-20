<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // destinataire
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete(); // expéditeur
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info');
            $table->string('icon')->default('notifications'); // Material Symbol icon name
            $table->string('link')->nullable(); // lien cliquable dans la notif
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
