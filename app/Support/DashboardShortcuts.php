<?php

namespace App\Support;

use App\Models\Finance\PaymentReminder;
use App\Models\User;
use App\Services\Workflow\ApprovalWorkflowService;

/**
 * The dashboard's quick-access tiles. Each tile is shown only when the user
 * holds the ability that the target page itself checks, so a tile never leads
 * to a 403.
 */
class DashboardShortcuts
{
    public function __construct(private readonly ApprovalWorkflowService $workflow) {}

    /**
     * @return list<array{key: string, url: string, icon: string, tone: string, badge: ?int}>
     */
    public function for(User $user): array
    {
        $tiles = [
            ['visit', ['merchandising.create'], 'merchandising.visits.create', 'ri-camera-lens-line', 'cyan'],
            ['leave', ['leaves.create'], 'leave-requests.create', 'ri-calendar-event-line', 'success'],
            ['mission', ['missions.create'], 'mission-requests.create', 'ri-briefcase-4-line', 'info'],
            ['approvals', ['leaves.approve', 'missions.approve', 'leaves.approve_any', 'missions.approve_any'], 'approvals.index', 'ri-checkbox-multiple-line', 'warning'],
            ['task', ['tasks.create'], 'tasks.create', 'ri-task-line', 'primary'],
            ['files', ['files.create'], 'files.index', 'ri-folder-3-line', 'warning'],
            ['order', ['orders.create'], 'orders.create', 'ri-shopping-cart-2-line', 'purple'],
            ['stock', ['stock.record'], 'admin.stock-movements.create', 'ri-arrow-left-right-line', 'cyan'],
            ['assets', ['assets.view'], 'admin.assets.index', 'ri-computer-line', 'info'],
            ['attendance', ['face-device-events.view'], 'admin.attendance-report.index', 'ri-bar-chart-2-line', 'success'],
            ['merch_reports', ['merchandising.view_all'], 'merchandising.reports.index', 'ri-bar-chart-box-line', 'primary'],
            ['payments', ['payment-reminders.manage'], 'admin.payment-reminders.index', 'ri-bank-card-line', 'danger'],
            ['user', ['users.create'], 'admin.users.create', 'ri-user-add-line', 'primary'],
            ['org_chart', ['org-chart.manage'], 'admin.org-chart.index', 'ri-organization-chart', 'purple'],
        ];

        $shortcuts = [];

        foreach ($tiles as [$key, $abilities, $route, $icon, $tone]) {
            if (! $user->canAny($abilities)) {
                continue;
            }

            $shortcuts[] = [
                'key' => $key,
                'url' => route($route),
                'icon' => $icon,
                'tone' => $tone,
                'badge' => $this->badge($key, $user),
            ];
        }

        return $shortcuts;
    }

    private function badge(string $key, User $user): ?int
    {
        $count = match ($key) {
            'approvals' => $this->workflow->pendingStepsFor($user)->count(),
            'payments' => PaymentReminder::needingAttention()->count(),
            default => 0,
        };

        return $count > 0 ? $count : null;
    }
}
