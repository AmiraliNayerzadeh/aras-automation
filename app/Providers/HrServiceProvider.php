<?php

namespace App\Providers;

use App\Services\Workflow\ApprovalWorkflowService;
use App\Support\DashboardWidgetRegistry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class HrServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(DashboardWidgetRegistry $registry, ApprovalWorkflowService $workflow): void
    {
        $registry->register('approvals.pending', function () use ($workflow) {
            $user = Auth::user();

            if (! $user) {
                return [];
            }

            $count = $workflow->pendingStepsFor($user)->count();

            if ($count === 0) {
                return [];
            }

            return [[
                'label' => __('app.inbox_pending_approval', ['count' => $count]),
                'url' => route('approvals.index'),
                'count' => $count,
            ]];
        });
    }
}
