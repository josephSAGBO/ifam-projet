<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use Tests\TestCase;

class IsolatedMySQLSiteMigrationTest extends TestCase
{
    private const TEST_DB = 'ifam_isolated_migration_test';

    private const CONNECTION_NAME = 'mysql_isolated_test';

    private function getRootPdo(): ?PDO
    {
        try {
            $host = config('database.connections.mysql.host', '127.0.0.1');
            $port = config('database.connections.mysql.port', '3306');
            $user = config('database.connections.mysql.username', 'root');
            $pass = config('database.connections.mysql.password', '');

            return new PDO("mysql:host={$host};port={$port}", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        $pdo = $this->getRootPdo();
        if (! $pdo) {
            $this->markTestSkipped('MySQL server is not available for isolated test.');
        }

        $pdo->exec('CREATE DATABASE IF NOT EXISTS `'.self::TEST_DB.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');

        config([
            'database.connections.'.self::CONNECTION_NAME => [
                'driver' => 'mysql',
                'host' => config('database.connections.mysql.host', '127.0.0.1'),
                'port' => config('database.connections.mysql.port', '3306'),
                'database' => self::TEST_DB,
                'username' => config('database.connections.mysql.username', 'root'),
                'password' => config('database.connections.mysql.password', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ],
        ]);

        DB::purge(self::CONNECTION_NAME);
    }

    protected function tearDown(): void
    {
        $pdo = $this->getRootPdo();
        if ($pdo) {
            try {
                DB::purge(self::CONNECTION_NAME);
                $pdo->exec('DROP DATABASE IF EXISTS `'.self::TEST_DB.'`;');
            } catch (\Throwable $e) {
                // Ignore cleanup errors
            }
        }

        parent::tearDown();
    }

    public function test_isolated_mysql_migrations_backfill_constraints_and_rollback(): void
    {
        $conn = DB::connection(self::CONNECTION_NAME);

        // 1. Run migrations up to 2026_10_14_143849 (all pre-multi-site migrations)
        $migrationFiles = glob(base_path('database/migrations/*.php'));
        sort($migrationFiles);

        $preSiteMigrations = array_filter($migrationFiles, function ($path) {
            $filename = basename($path);

            return strcmp($filename, '2026_10_19_164954') < 0;
        });

        // Setup schema builder for isolated connection
        $schema = $conn->getSchemaBuilder();

        // Create migrations tracking table
        if (! $schema->hasTable('migrations')) {
            $schema->create('migrations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('migration');
                $table->integer('batch');
            });
        }

        $defaultConn = DB::getDefaultConnection();
        DB::setDefaultConnection(self::CONNECTION_NAME);

        try {
            foreach ($preSiteMigrations as $file) {
                $migration = require $file;
                $migration->up();
                $conn->table('migrations')->insert([
                    'migration' => basename($file, '.php'),
                    'batch' => 1,
                ]);
            }

            // 2. Insert pre-existing business records before multi-site migrations
            $userId = $conn->table('users')->insertGetId([
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'email' => 'jean.dupont@test.com',
                'password' => 'secret',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $studentProfileId = $conn->table('student_profiles')->insertGetId([
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $formationId = $conn->table('formations')->insertGetId([
                'name' => 'Formation Preexistante',
                'prix' => 150000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sessionId = $conn->table('academic_sessions')->insertGetId([
                'name' => 'Rentrée 2026 Préexistante',
                'normalized_name' => 'rentree 2026 preexistante',
                'start_date' => '2026-09-01',
                'end_date' => '2027-06-30',
                'status' => 'en_cours',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $enrollmentId = $conn->table('enrollments')->insertGetId([
                'student_profile_id' => $studentProfileId,
                'formation_id' => $formationId,
                'academic_session_id' => $sessionId,
                'montant_du' => 150000,
                'status' => 'active',
                'enrollment_date' => '2026-09-05',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $roomId = $conn->table('rooms')->insertGetId([
                'name' => 'Salle A Preexistante',
                'description' => 'Salle principale',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $expenseCatId = $conn->table('expense_categories')->insertGetId([
                'name' => 'Fournitures',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $expenseId = $conn->table('expenses')->insertGetId([
                'title' => 'Achat papier',
                'amount' => 5000,
                'expense_date' => '2026-09-10',
                'expense_category_id' => $expenseCatId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $alumniId = $conn->table('alumnis')->insertGetId([
                'student_profile_id' => $studentProfileId,
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'jean.dupont@test.com',
                'is_approved' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $contactMessageId = $conn->table('contact_messages')->insertGetId([
                'user_id' => $userId,
                'subject' => 'Question',
                'message' => 'Contenu du message',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $partnershipId = $conn->table('partnerships')->insertGetId([
                'nom' => 'Partenaire Existant',
                'type_partenariat' => 'Stage',
                'statut' => 'actif',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3. Now run the 3 multi-site migrations
            $migA = require base_path('database/migrations/2026_10_19_164954_create_sites_table.php');
            $migA->up();

            $migB = require base_path('database/migrations/2026_10_19_164955_add_site_id_to_site_owned_tables.php');
            $migB->up();

            $migC = require base_path('database/migrations/2026_10_19_164956_make_site_required_on_site_owned_tables.php');
            $migC->up();

            // 4. VERIFY: Cotonou site creation & backfill
            $cotonou = $conn->table('sites')->where('nom', 'Cotonou')->first();
            $this->assertNotNull($cotonou, 'Cotonou site must be created');
            $this->assertSame('Cotonou', $cotonou->ville);
            $this->assertEquals(1, $cotonou->est_actif);
            $cotonouId = $cotonou->id;

            // Verify session site_id
            $session = $conn->table('academic_sessions')->where('id', $sessionId)->first();
            $this->assertEquals($cotonouId, $session->site_id, 'Academic session must be assigned to Cotonou');

            // Verify enrollment site_id copied from academic session
            $enrollment = $conn->table('enrollments')->where('id', $enrollmentId)->first();
            $this->assertEquals($cotonouId, $enrollment->site_id, 'Enrollment must copy site_id from session');

            // Verify rooms, expenses, alumnis, contact_messages
            $this->assertEquals($cotonouId, $conn->table('rooms')->where('id', $roomId)->value('site_id'));
            $this->assertEquals($cotonouId, $conn->table('expenses')->where('id', $expenseId)->value('site_id'));
            $this->assertEquals($cotonouId, $conn->table('alumnis')->where('id', $alumniId)->value('site_id'));
            $this->assertEquals($cotonouId, $conn->table('contact_messages')->where('id', $contactMessageId)->value('site_id'));

            // Verify partnerships.site_id is NULL (nullable)
            $this->assertNull($conn->table('partnerships')->where('id', $partnershipId)->value('site_id'));

            // 5. VERIFY: NOT NULL constraints in MySQL schema
            $requiredTables = ['academic_sessions', 'enrollments', 'rooms', 'expenses', 'alumnis', 'contact_messages'];
            foreach ($requiredTables as $table) {
                $columnInfo = $conn->selectOne("
                    SELECT IS_NULLABLE, COLUMN_TYPE
                    FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'site_id'
                ", [self::TEST_DB, $table]);

                $this->assertNotNull($columnInfo, "Column site_id must exist in {$table}");
                $this->assertSame('NO', $columnInfo->IS_NULLABLE, "Column site_id must be NOT NULL in {$table}");
            }

            // Verify partnerships.site_id is nullable in MySQL schema
            $partnershipCol = $conn->selectOne("
                SELECT IS_NULLABLE
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'partnerships' AND COLUMN_NAME = 'site_id'
            ", [self::TEST_DB]);
            $this->assertSame('YES', $partnershipCol->IS_NULLABLE, 'Column site_id in partnerships must be nullable');

            // 6. VERIFY: Foreign Key RESTRICT constraint
            $restrictTriggered = false;
            try {
                $conn->table('sites')->where('id', $cotonouId)->delete();
            } catch (QueryException $e) {
                $restrictTriggered = true;
            }
            $this->assertTrue($restrictTriggered, 'Foreign key constraint must prevent deleting site with attached records');

            // 7. VERIFY: Rollback & business data conservation
            // Rollback Migration C (make site required)
            $migC->down();
            foreach ($requiredTables as $table) {
                $col = $conn->selectOne("
                    SELECT IS_NULLABLE
                    FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'site_id'
                ", [self::TEST_DB, $table]);
                $this->assertSame('YES', $col->IS_NULLABLE, "Column site_id must become nullable after rollback of {$table}");
            }

            // Rollback Migration B (add site_id columns)
            $migB->down();
            foreach ([...$requiredTables, 'partnerships'] as $table) {
                $exists = $conn->selectOne("
                    SELECT COUNT(*) as cnt
                    FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = 'site_id'
                ", [self::TEST_DB, $table])->cnt;
                $this->assertEquals(0, $exists, "Column site_id must be dropped from {$table}");
            }

            // Rollback Migration A (create sites and site_user)
            $migA->down();
            $this->assertFalse($schema->hasTable('site_user'));
            $this->assertFalse($schema->hasTable('sites'));

            // Verify pre-existing data is intact after full rollback
            $this->assertSame('Formation Preexistante', $conn->table('formations')->where('id', $formationId)->value('name'));
            $this->assertSame('Rentrée 2026 Préexistante', $conn->table('academic_sessions')->where('id', $sessionId)->value('name'));
            $this->assertEquals(150000, (float) $conn->table('enrollments')->where('id', $enrollmentId)->value('montant_du'));
            $this->assertSame('Salle A Preexistante', $conn->table('rooms')->where('id', $roomId)->value('name'));
            $this->assertEquals(5000, (float) $conn->table('expenses')->where('id', $expenseId)->value('amount'));
            $this->assertSame('Dupont', $conn->table('alumnis')->where('id', $alumniId)->value('nom'));
            $this->assertSame('Question', $conn->table('contact_messages')->where('id', $contactMessageId)->value('subject'));
            $this->assertSame('Partenaire Existant', $conn->table('partnerships')->where('id', $partnershipId)->value('nom'));

        } finally {
            DB::setDefaultConnection($defaultConn);
            DB::purge(self::CONNECTION_NAME);
        }
    }
}
