<?php

namespace App\Console\Commands;

use App\Models\ServiceProfile;
use App\Models\ServiceReport;
use App\Models\Site;
use App\Models\UserAssignment;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckServiceReportsDeadlinesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'reports:check-deadlines {--date= : Date de référence pour le test (Y-m-d)}';

    /**
     * The console command description.
     */
    protected $description = 'Vérifie les échéances des rapports de services (rappels à J-2 et alertes de retard à la Direction).';

    public function handle(NotificationService $notificationService): int
    {
        $today = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $this->info("Exécution du contrôle des échéances pour la date du {$today->format('Y-m-d')}...");

        $monthlyDeadlineDay = (int) config('reports.deadlines.mensuel.day_of_month', 5);
        $reminderDays = (int) config('reports.reminder_days_before', 2);
        $reminderDay = $monthlyDeadlineDay - $reminderDays; // ex: 3 si échéance le 5

        // Période mensuelle précédente
        $debutMoisPrecedent = $today->copy()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $finMoisPrecedent = $today->copy()->subMonthNoOverflow()->endOfMonth()->toDateString();
        $dateEcheance = Carbon::create($today->year, $today->month, $monthlyDeadlineDay)->endOfDay();

        $services = ServiceProfile::active()->get();

        foreach ($services as $service) {
            // Sites assignés pour ce service
            $siteIds = UserAssignment::where('service_profile_id', $service->id)
                ->pluck('site_id')
                ->unique();

            if ($siteIds->isEmpty()) {
                $siteIds = collect([null]);
            }

            foreach ($siteIds as $siteId) {
                $site = $siteId ? Site::find($siteId) : null;

                // Vérifier si un rapport mensuel a déjà été envoyé pour la période
                $reportExists = ServiceReport::where('service_profile_id', $service->id)
                    ->when($siteId !== null, fn ($q) => $q->where('site_id', $siteId), fn ($q) => $q->whereNull('site_id'))
                    ->where('type', ServiceReport::TYPE_MENSUEL)
                    ->where('periode_debut', $debutMoisPrecedent)
                    ->whereIn('statut', [ServiceReport::STATUT_ENVOYE, ServiceReport::STATUT_LU])
                    ->exists();

                if ($reportExists) {
                    continue;
                }

                // 1. Rappel à J-2 de l'échéance
                if ($today->day === $reminderDay) {
                    $notificationService->rappelEcheanceRapport(
                        $service,
                        ServiceReport::TYPE_MENSUEL,
                        Carbon::parse($debutMoisPrecedent),
                        Carbon::parse($finMoisPrecedent),
                        $dateEcheance,
                        $site
                    );
                    $this->line("Rappel envoyé pour le service {$service->name}".($site ? " ({$site->nom})" : ''));
                }

                // 2. Alerte de retard après l'échéance
                if ($today->day > $monthlyDeadlineDay) {
                    $notificationService->rapportEnRetard(
                        $service,
                        ServiceReport::TYPE_MENSUEL,
                        Carbon::parse($debutMoisPrecedent),
                        Carbon::parse($finMoisPrecedent),
                        $site
                    );
                    $this->warn("Alerte de retard envoyée à la Direction pour le service {$service->name}".($site ? " ({$site->nom})" : ''));
                }
            }
        }

        $this->info('Contrôle des échéances terminé.');

        return Command::SUCCESS;
    }
}
