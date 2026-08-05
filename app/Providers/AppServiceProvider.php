<?php

namespace App\Providers;

use App\Http\Middleware\SetLocale;
use App\Support\DashboardWidgetRegistry;
use Illuminate\Auth\Events\Login;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DashboardWidgetRegistry::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Event::listen(function (Login $event) {
            if ($event->user->locale && in_array($event->user->locale, SetLocale::SUPPORTED_LOCALES, true)) {
                session(['locale' => $event->user->locale]);
            }
        });
    }
}
