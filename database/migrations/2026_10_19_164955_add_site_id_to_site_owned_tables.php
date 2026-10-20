<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        foreach ([...self::REQUIRED_SITE_TABLES, 'partnerships'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->foreignId('site_id')
                    ->nullable()
                    ->constrained('sites')
                    ->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach ([...self::REQUIRED_SITE_TABLES, 'partnerships'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropConstrainedForeignId('site_id');
            });
        }
    }
};
