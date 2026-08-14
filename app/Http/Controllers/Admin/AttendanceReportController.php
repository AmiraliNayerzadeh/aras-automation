<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hr\FaceDeviceEvent;
use App\Models\Organization\Branch;
use App\Models\Organization\Department;
use App\Models\User;
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

    public function index(Request $request): View
    {
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->input('date_from')) : now()->startOfMonth();
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->input('date_to')) : now();

        $events = FaceDeviceEvent::query()
            ->whereNotNull('user_id')
            ->with('user')
            ->whereBetween('event_time', [$dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()])
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->input('user_id')))
            ->when($request->filled('department_id'), fn ($q) => $q->whereHas('user', fn ($qq) => $qq->where('department_id', $request->input('department_id'))))
            ->when($request->filled('branch_id'), fn ($q) => $q->whereHas('user', fn ($qq) => $qq->where('branch_id', $request->input('branch_id'))))
            ->orderBy('event_time')
            ->get();

        $daily = $this->buildDailyEntries($events);

        $viewData = [
            'dateFrom' => $dateFrom->toDateString(),
            'dateTo' => $dateTo->toDateString(),
            'users' => User::orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'branches' => Branch::orderBy('name')->get(['id', 'name']),
        ];

        if ($request->filled('user_id')) {
            $rows = $daily->sortBy('date')->values();

            return view('admin.attendance-report.index', $viewData + [
                'mode' => 'detail',
                'employee' => $rows->first()['user'] ?? User::find($request->input('user_id')),
                'rows' => $rows,
                'chartData' => [
                    'labels' => $rows->pluck('date')->all(),
                    'values' => $rows->map(fn ($r) => $r['duration_minutes'] !== null ? round($r['duration_minutes'] / 60, 2) : 0)->all(),
                ],
            ]);
        }

        $summary = $daily
            ->groupBy('user_id')
            ->map(function (Collection $rows) {
                $complete = $rows->whereNotNull('duration_minutes');
                $totalMinutes = $complete->sum('duration_minutes');

                return [
                    'user' => $rows->first()['user'],
                    'present_days' => $rows->count(),
                    'complete_days' => $complete->count(),
                    'total_minutes' => $totalMinutes,
                    'avg_minutes' => $complete->count() > 0 ? (int) round($totalMinutes / $complete->count()) : null,
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

    /**
     * Pair events per user per day into check-in/check-out entries: the
     * earliest and latest device event of the day. A single event for a day
     * is kept but flagged as incomplete (duration_minutes = null).
     */
    private function buildDailyEntries(Collection $events): Collection
    {
        return $events
            ->groupBy(fn (FaceDeviceEvent $e) => $e->user_id.'|'.$e->event_time->toDateString())
            ->map(function (Collection $dayEvents) {
                $first = $dayEvents->first();
                $checkIn = $dayEvents->min('event_time');
                $checkOut = $dayEvents->max('event_time');
                $isComplete = $dayEvents->count() > 1;

                return [
                    'user_id' => $first->user_id,
                    'user' => $first->user,
                    'date' => $first->event_time->toDateString(),
                    'check_in' => $checkIn,
                    'check_out' => $isComplete ? $checkOut : null,
                    'duration_minutes' => $isComplete ? $checkIn->diffInMinutes($checkOut) : null,
                    'event_count' => $dayEvents->count(),
                ];
            })
            ->values();
    }
}
