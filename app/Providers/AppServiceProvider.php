<?php

namespace App\Providers;

use App\Models\AcademicSession;
use App\Models\Notification;
use App\Models\ServiceReport;
use App\Policies\ServiceReportPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(ServiceReport::class, ServiceReportPolicy::class);

        // Injecter la session active dans le layout d'administration
        View::composer('layouts.admin', function ($view) {
            $view->with('activeSession', AcademicSession::where('is_current', true)->first());
        });

        // Compteur de notifications non lues pour tous les layouts connectés
        View::composer(['layouts.admin', 'layouts.role'], function ($view) {
            if (auth()->check()) {
                $view->with(
                    'unreadNotifications',
                    Notification::where('user_id', auth()->id())
                        ->whereNull('read_at')
                        ->count()
                );
            }
        });
    }
}
