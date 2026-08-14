<?php

namespace App\Providers;

use App\Http\Middleware\SetLocale;
use App\Models\FileManager\FileEntry;
use App\Models\FileManager\Folder;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\MissionRequest;
use App\Models\Orders\OrderStageLog;
use App\Models\Tasks\Task;
use App\Models\User;
use App\Support\DashboardWidgetRegistry;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Relations\Relation;
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

        Relation::morphMap([
            'leave_request' => LeaveRequest::class,
            'mission_request' => MissionRequest::class,
            'user' => User::class,
            'order_stage_log' => OrderStageLog::class,
            'task' => Task::class,
            'folder' => Folder::class,
            'file' => FileEntry::class,
        ]);

        Event::listen(function (Login $event) {
            if ($event->user->locale && in_array($event->user->locale, SetLocale::SUPPORTED_LOCALES, true)) {
                session(['locale' => $event->user->locale]);
            }
        });
    }
}
