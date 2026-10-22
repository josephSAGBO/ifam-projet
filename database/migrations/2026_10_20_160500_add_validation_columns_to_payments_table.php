<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'status')) {
                $table->string('status', 30)->default('en_attente')->after('notes');
            }
            if (! Schema::hasColumn('payments', 'validated_by')) {
                $table->foreignId('validated_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('payments', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('validated_by');
            }
            if (! Schema::hasColumn('payments', 'validation_notes')) {
                $table->text('validation_notes')->nullable()->after('validated_at');
            }
        });

        // Régularisation historique : tous les paiements préexistants sont considérés comme validés
        DB::table('payments')->where('status', 'en_attente')->update([
            'status' => 'valide',
            'validated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'validation_notes')) {
                $table->dropColumn('validation_notes');
            }
            if (Schema::hasColumn('payments', 'validated_at')) {
                $table->dropColumn('validated_at');
            }
            if (Schema::hasColumn('payments', 'validated_by')) {
                $table->dropConstrainedForeignId('validated_by');
            }
            if (Schema::hasColumn('payments', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
