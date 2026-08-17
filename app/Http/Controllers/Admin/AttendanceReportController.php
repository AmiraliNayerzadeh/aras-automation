<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\Organization\Branch;
use App\Models\Organization\Department;
use App\Models\User;
use App\Services\AttendanceCalculator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AttendanceReportController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:face-device-events.view'),
        ];
    }

    public function index(Request $request, AttendanceCalculator $calculator): View
    {
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->input('date_from')) : now()->startOfMonth();
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->input('date_to')) : now();

        $users = User::query()
            ->with('workShifts')
            ->where('status', UserStatus::Active->value)
            ->when($request->filled('user_id'), fn ($q) => $q->where('id', $request->input('user_id')))
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->input('department_id')))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->input('branch_id')))
            ->orderBy('name')
            ->get();

        $daily = $calculator->forDateRange($users, $dateFrom, $dateTo);

        $viewData = [
            'dateFrom' => $dateFrom->toDateString(),
            'dateTo' => $dateTo->toDateString(),
            'users' => User::where('status', UserStatus::Active->value)->orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
        ];

        if ($request->filled('user_id')) {
            $rows = $daily->sortBy('date')->values();

            return view('admin.attendance-report.index', $viewData + [
                'mode' => 'detail',
                'employee' => $users->first(),
                'rows' => $rows,
                'chartData' => [
                    'labels' => $rows->pluck('date')->all(),
                    'values' => $rows->map(fn ($r) => $r['worked_minutes'] !== null ? round($r['worked_minutes'] / 60, 2) : 0)->all(),
                ],
            ]);
        }

        $summary = $daily
            ->groupBy('user_id')
            ->map(function (Collection $rows) {
                $present = $rows->where('status', 'present');

                return [
                    'user' => $rows->first()['user'],
                    'present_days' => $present->count(),
                    'absent_days' => $rows->where('status', 'absent')->count(),
                    'on_leave_days' => $rows->where('status', 'on_leave')->count(),
                    'remote_days' => $rows->where('status', 'remote')->count(),
                    'incomplete_days' => $rows->where('status', 'incomplete')->count(),
                    'total_minutes' => $present->sum('worked_minutes'),
                    'avg_minutes' => $present->count() > 0 ? (int) round($present->sum('worked_minutes') / $present->count()) : null,
                    'total_overtime_minutes' => $present->sum('overtime_minutes'),
                    'total_shortfall_minutes' => $present->sum('shortfall_minutes'),
                ];
            })
            ->sortByDesc('total_minutes')
            ->values();

        return view('admin.attendance-report.index', $viewData + [
            'mode' => 'summary',
            'summary' => $summary,
            'chartData' => [
                'labels' => $summary->take(20)->map(fn ($s) => $s['user']?->name ?? '—')->all(),
                'values' => $summary->take(20)->map(fn ($s) => round($s['total_minutes'] / 60, 2))->all(),
            ],
        ]);
    }
}
