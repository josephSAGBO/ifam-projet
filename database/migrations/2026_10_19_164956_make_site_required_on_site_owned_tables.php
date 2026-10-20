<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const REQUIRED_SITE_TABLES = [
        'academic_sessions',
        'enrollments',
        'rooms',
        'expenses',
        'alumnis',
        'contact_messages',
    ];

    public function up(): void
    {
        $siteId = DB::table('sites')
            ->where('nom', 'Cotonou')
            ->orderBy('id')
            ->value('id');

        if ($siteId === null) {
            $siteId = DB::table('sites')->insertGetId([
                'nom' => 'Cotonou',
                'ville' => 'Cotonou',
                'est_actif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('academic_sessions')->update(['site_id' => $siteId]);

        foreach (DB::table('enrollments')->get(['id', 'academic_session_id']) as $enrollment) {
            $sessionSiteId = DB::table('academic_sessions')
                ->where('id', $enrollment->academic_session_id)
                ->value('site_id');

            if ($sessionSiteId === null) {
                throw new RuntimeException("Academic session {$enrollment->academic_session_id} has no site.");
            }

            DB::table('enrollments')
                ->where('id', $enrollment->id)
                ->update(['site_id' => $sessionSiteId]);
        }

        foreach (array_diff(self::REQUIRED_SITE_TABLES, ['academic_sessions', 'enrollments']) as $tableName) {
            DB::table($tableName)->update(['site_id' => $siteId]);
        }

        foreach (self::REQUIRED_SITE_TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('site_id')->nullable(false)->change();
            });
        }
    }

    public function down(): void
    {
        foreach (self::REQUIRED_SITE_TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->unsignedBigInteger('site_id')->nullable()->change();
            });
        }
    }
};
