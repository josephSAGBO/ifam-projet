<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $normalizedNames = [];
        $sessionIdsByName = [];

        foreach (DB::table('academic_sessions')->select('id', 'name')->orderBy('id')->get() as $session) {
            $normalizedName = Str::lower(Str::squish($session->name));
            if (isset($sessionIdsByName[$normalizedName])) {
                throw new RuntimeException('Academic sessions contain duplicate names after normalization.');
            }

            $sessionIdsByName[$normalizedName] = $session->id;
            $normalizedNames[$session->id] = $normalizedName;
        }

        Schema::table('courses', function (Blueprint $table): void {
            $table->string('statut')->default('en_cours')->after('name');
        });

        Schema::table('academic_sessions', function (Blueprint $table): void {
            $table->string('normalized_name')->nullable();
        });

        foreach ($normalizedNames as $sessionId => $normalizedName) {
            DB::table('academic_sessions')
                ->where('id', $sessionId)
                ->update(['normalized_name' => $normalizedName]);
        }

        Schema::table('academic_sessions', function (Blueprint $table): void {
            $table->string('normalized_name')->nullable(false)->change();
            $table->unique('normalized_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_sessions', function (Blueprint $table): void {
            $table->dropUnique('academic_sessions_normalized_name_unique');
            $table->dropColumn('normalized_name');
        });

        Schema::table('courses', function (Blueprint $table): void {
            $table->dropColumn('statut');
        });
    }
};
