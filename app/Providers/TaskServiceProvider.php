<?php

namespace App\Providers;

use App\Models\Tasks\Task;
use App\Support\DashboardWidgetRegistry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class TaskServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(DashboardWidgetRegistry $registry): void
    {
        $registry->register('tasks.due_soon', function () {
            $user = Auth::user();

            if (! $user) {
                return [];
            }

            $tasks = Task::query()
                ->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id))
                ->whereNotIn('status', ['done', 'cancelled'])
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<=', now()->addDays(3))
                ->orderBy('due_date')
                ->get();

            return $tasks->map(fn (Task $task) => [
                'label' => __('app.task_due_soon', ['title' => $task->title, 'date' => $task->due_date->format('Y-m-d')]),
                'url' => route('tasks.show', $task),
                'count' => 1,
            ])->all();
        });
    }
}
