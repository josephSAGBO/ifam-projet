<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SiteMigrationBackfillTest extends TestCase
{
    public function test_backfill_migration_assigns_existing_records_to_cotonou_and_requires_a_site(): void
    {
        $defaultConnection = config('database.default');
        config([
            'database.connections.site_migration_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('site_migration_test');
        DB::setDefaultConnection('site_migration_test');

        try {
            Schema::create('sites', function (Blueprint $table): void {
                $table->id();
                $table->string('nom');
                $table->string('ville');
                $table->boolean('est_actif')->default(true);
                $table->timestamps();
            });

            Schema::create('academic_sessions', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('site_id')->nullable();
            });

            Schema::create('enrollments', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('academic_session_id');
                $table->unsignedBigInteger('site_id')->nullable();
            });

            foreach (['rooms', 'expenses', 'alumnis', 'contact_messages', 'partnerships'] as $tableName) {
                Schema::create($tableName, function (Blueprint $table): void {
                    $table->id();
                    $table->unsignedBigInteger('site_id')->nullable();
                });
            }

            DB::table('academic_sessions')->insert(['id' => 20, 'site_id' => null]);
            DB::table('enrollments')->insert(['id' => 30, 'academic_session_id' => 20, 'site_id' => null]);

            foreach (['rooms', 'expenses', 'alumnis', 'contact_messages', 'partnerships'] as $tableName) {
                DB::table($tableName)->insert(['id' => 40, 'site_id' => null]);
            }

            $migration = require base_path('database/migrations/2026_10_19_164956_make_site_required_on_site_owned_tables.php');
            $migration->up();

            $siteId = (int) DB::table('sites')->where('nom', 'Cotonou')->value('id');
            $this->assertGreaterThan(0, $siteId);
            $this->assertSame($siteId, (int) DB::table('academic_sessions')->where('id', 20)->value('site_id'));
            $this->assertSame($siteId, (int) DB::table('enrollments')->where('id', 30)->value('site_id'));
            $sessionSiteColumn = collect(DB::select("PRAGMA table_info('academic_sessions')"))
                ->firstWhere('name', 'site_id');
            $enrollmentSiteColumn = collect(DB::select("PRAGMA table_info('enrollments')"))
                ->firstWhere('name', 'site_id');
            $this->assertSame(1, (int) $sessionSiteColumn->notnull);
            $this->assertSame(1, (int) $enrollmentSiteColumn->notnull);

            foreach (['rooms', 'expenses', 'alumnis', 'contact_messages'] as $tableName) {
                $this->assertSame($siteId, (int) DB::table($tableName)->where('id', 40)->value('site_id'));
                $siteColumn = collect(DB::select("PRAGMA table_info('{$tableName}')"))
                    ->firstWhere('name', 'site_id');
                $this->assertSame(1, (int) $siteColumn->notnull);
            }

            $this->assertNull(DB::table('partnerships')->where('id', 40)->value('site_id'));
        } finally {
            DB::setDefaultConnection($defaultConnection);
            DB::purge('site_migration_test');
        }
    }
}
