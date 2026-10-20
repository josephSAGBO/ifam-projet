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
        Schema::create('attendance_sheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('statut')->default('ouverte');
            $table->timestamps();

            $table->unique(['course_id', 'date']);
        });

        DB::table('attendances')
            ->select('course_id', 'date')
            ->distinct()
            ->orderBy('course_id')
            ->orderBy('date')
            ->get()
            ->each(function (object $attendance): void {
                DB::table('attendance_sheets')->insert([
                    'course_id' => $attendance->course_id,
                    'date' => $attendance->date,
                    'statut' => 'cloturee',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sheets');
    }
};
