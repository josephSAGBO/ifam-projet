<?php

namespace App\Console\Commands;

use App\Models\Notification;
use Illuminate\Console\Command;

class PruneOldNotificationsCommand extends Command
{
    /**
     * Supprime les notifications lues de plus de 90 jours.
     */
    protected $signature = 'notifications:prune {--days=90 : Nombre de jours de rétention}';

    protected $description = 'Supprime les notifications lues depuis plus de N jours (défaut : 90)';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $count = Notification::whereNotNull('read_at')
            ->where('read_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Supprimé {$count} notification(s) lue(s) de plus de {$days} jours.");

        return self::SUCCESS;
    }
}
