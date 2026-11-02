<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserStatus;
use App\Exports\AttendanceReportExport;
use App\Http\Controllers\Controller;
use App\Models\Organization\Branch;
use App\Models\Organization\Department;
use App\Models\User;
use App\Services\AttendanceCalculator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        [$dateFrom, $dateTo] = $this->dateRange($request);
        $users = $this->reportUsers($request);

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

        $summary = $calculator->summarize($daily);

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
     * With a user_id: that employee's own report. Otherwise a group workbook
     * for the current filters: a summary sheet plus one sheet per employee.
     */
    public function excel(Request $request, AttendanceCalculator $calculator): BinaryFileResponse
    {
        $request->validate([
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);

        [$dateFrom, $dateTo] = $this->dateRange($request);
        $users = $this->reportUsers($request)->load('department');

        abort_if($users->isEmpty(), 404);

        $single = $request->filled('user_id');
        $daily = $calculator->forDateRange($users, $dateFrom, $dateTo);

        $export = new AttendanceReportExport($calculator->summarize($daily), $daily->groupBy('user_id'), $single, $dateFrom, $dateTo);

        $scope = $single ? ($users->first()->employee_number ?: $users->first()->id).'_' : '';

        return Excel::download($export, 'attendance_'.$scope.$dateFrom->toDateString().'_'.$dateTo->toDateString().'.xlsx');
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function dateRange(Request $request): array
    {
        return [
            $request->filled('date_from') ? Carbon::parse($request->input('date_from')) : now()->startOfMonth(),
            $request->filled('date_to') ? Carbon::parse($request->input('date_to')) : now(),
        ];
    }

    /**
     * @return EloquentCollection<int, User>
     */
    protected function reportUsers(Request $request): EloquentCollection
    {
        return User::query()
            ->with('workShifts')
            ->where('status', UserStatus::Active->value)
            ->when($request->filled('user_id'), fn ($q) => $q->where('id', $request->input('user_id')))
            ->when($request->filled('department_id'), fn ($q) => $q->where('department_id', $request->input('department_id')))
            ->when($request->filled('branch_id'), fn ($q) => $q->where('branch_id', $request->input('branch_id')))
            ->orderBy('name')
            ->get();
    }
}
