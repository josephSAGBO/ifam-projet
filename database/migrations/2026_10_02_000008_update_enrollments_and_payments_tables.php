<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update enrollments table
        Schema::table('enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('enrollments', 'prix_formation')) {
                $table->decimal('prix_formation', 10, 2)->default(0)->after('academic_session_id');
            }
            if (! Schema::hasColumn('enrollments', 'bourse_type')) {
                $table->enum('bourse_type', ['aucune', 'pourcentage', 'montant'])->default('aucune')->after('prix_formation');
            }
            if (! Schema::hasColumn('enrollments', 'bourse_valeur')) {
                $table->decimal('bourse_valeur', 10, 2)->default(0)->after('bourse_type');
            }
            if (! Schema::hasColumn('enrollments', 'montant_du')) {
                $table->decimal('montant_du', 10, 2)->default(0)->after('bourse_valeur');
            }
        });

        // 2. Migrate existing enrollments
        $enrollments = DB::table('enrollments')->get();
        foreach ($enrollments as $enr) {
            $formation = DB::table('formations')->where('id', $enr->formation_id)->first();
            $prixFormation = ($formation && $formation->prix > 0) ? $formation->prix : 450000.00;

            // Check if there was any discount in discounts table
            $discount = null;
            if (Schema::hasTable('discounts')) {
                $discount = DB::table('discounts')->where('student_profile_id', $enr->student_profile_id)->first();
            }

            $bourseType = 'aucune';
            $bourseValeur = 0;
            $deduction = 0;

            if ($discount) {
                if ($discount->type === 'percentage') {
                    $bourseType = 'pourcentage';
                    $bourseValeur = $discount->value;
                    $deduction = $prixFormation * ($bourseValeur / 100);
                } else {
                    $bourseType = 'montant';
                    $bourseValeur = $discount->value;
                    $deduction = $bourseValeur;
                }
            }

            $montantDu = max(0, $prixFormation - $deduction);

            DB::table('enrollments')->where('id', $enr->id)->update([
                'prix_formation' => $prixFormation,
                'bourse_type' => $bourseType,
                'bourse_valeur' => $bourseValeur,
                'montant_du' => $montantDu,
            ]);
        }

        // 3. Update payments table
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'inscription_id')) {
                $table->foreignId('inscription_id')->nullable()->after('id')->constrained('enrollments')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('payments', 'montant')) {
                $table->decimal('montant', 10, 2)->nullable()->after('inscription_id');
            }
            if (! Schema::hasColumn('payments', 'date_paiement')) {
                $table->date('date_paiement')->nullable()->after('montant');
            }
            if (! Schema::hasColumn('payments', 'mode')) {
                $table->string('mode', 50)->default('especes')->after('date_paiement');
            }
            if (! Schema::hasColumn('payments', 'enregistre_par')) {
                $table->foreignId('enregistre_par')->nullable()->after('reference')->constrained('users')->nullOnDelete();
            }
        });

        // 4. Migrate existing payments
        $adminId = DB::table('users')->where('email', 'like', '%admin%')->value('id') ?? DB::table('users')->value('id');
        $payments = DB::table('payments')->get();

        foreach ($payments as $p) {
            $inscriptionId = $p->inscription_id ?? null;
            if (! $inscriptionId && isset($p->student_profile_id)) {
                // Find matching enrollment
                $enrollment = DB::table('enrollments')
                    ->where('student_profile_id', $p->student_profile_id)
                    ->orderByDesc('id')
                    ->first();
                $inscriptionId = $enrollment ? $enrollment->id : null;
            }

            $mode = 'especes';
            if (isset($p->method)) {
                $mode = match ($p->method) {
                    'cash' => 'especes',
                    'bank_transfer' => 'virement',
                    'card' => 'carte',
                    'check' => 'cheque',
                    default => 'autre',
                };
            }

            DB::table('payments')->where('id', $p->id)->update([
                'inscription_id' => $inscriptionId,
                'montant' => $p->montant ?? $p->amount_paid ?? 0,
                'date_paiement' => $p->date_paiement ?? $p->payment_date ?? now()->toDateString(),
                'mode' => $mode,
                'enregistre_par' => $p->enregistre_par ?? $adminId,
            ]);
        }

        // Make fee_id nullable if it was constrained, so payments are linked directly to inscriptions
        if (Schema::hasColumn('payments', 'fee_id')) {
            try {
                Schema::table('payments', function (Blueprint $table) {
                    $table->unsignedBigInteger('fee_id')->nullable()->change();
                });
            } catch (Throwable $e) {
            }
        }
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inscription_id');
            $table->dropConstrainedForeignId('enregistre_par');
            $table->dropColumn(['montant', 'date_paiement', 'mode']);
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['prix_formation', 'bourse_type', 'bourse_valeur', 'montant_du']);
        });
    }
};
