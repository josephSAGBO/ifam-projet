<?php

namespace App\Providers;

use App\Models\AcademicSession;
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
        // Injecter la session active dans le layout d'administration
        View::composer('layouts.admin', function ($view) {
            $view->with('activeSession', AcademicSession::where('is_current', true)->first());
        });
    }
}
