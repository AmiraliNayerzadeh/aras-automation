<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('app.title_attendance_report') }}</h2>
            <div class="d-flex gap-2 flex-wrap">
                @if ($mode === 'detail')
                    <a href="{{ route('admin.attendance-report.index', request()->except('user_id')) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                        <i class="ri-arrow-go-back-line"></i> {{ __('app.back_to_summary') }}
                    </a>
                @endif
                <a href="{{ route('admin.attendance-report.excel', array_merge(request()->query(), ['date_from' => $dateFrom, 'date_to' => $dateTo])) }}" class="btn btn-success-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-file-excel-2-line"></i> {{ $mode === 'detail' ? __('app.attendance_excel_employee') : __('app.attendance_excel_group') }}
                </a>
            </div>
        </div>
    </x-slot>

    @if (in_array(session('status'), ['attendance-corrected', 'attendance-correction-removed'], true))
        <div class="alert alert-success radius-8 mb-24">{{ __('app.flash_'.str_replace('-', '_', session('status'))) }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger radius-8 mb-24">{{ $errors->first() }}</div>
    @endif

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <x-input-label for="date_from" :value="__('orders.field_date_from')" />
                    <x-text-input id="date_from" name="date_from" type="date" class="mt-1 w-100" :value="$dateFrom" />
                </div>
                <div class="col-md-2">
                    <x-input-label for="date_to" :value="__('orders.field_date_to')" />
                    <x-text-input id="date_to" name="date_to" type="date" class="mt-1 w-100" :value="$dateTo" />
                </div>
                <div class="col-md-3">
                    <x-input-label for="user_id" :value="__('app.field_employee')" />
                    <select id="user_id" name="user_id" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <x-input-label for="department_id" :value="__('app.field_department')" />
                    <select id="department_id" name="department_id" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <x-input-label for="branch_id" :value="__('app.field_branch')" />
                    <select id="branch_id" name="branch_id" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary-600 radius-8 w-100 py-8">
                        <i class="ri-filter-3-line"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <div id="attendanceChart"></div>
        </div>
    </div>

    @php
        $statusBadge = fn (string $status) => match ($status) {
            'present' => 'text-success-600 bg-success-100',
            'absent' => 'text-danger-600 bg-danger-100',
            'on_leave' => 'text-info-600 bg-info-100',
            'remote' => 'text-primary-600 bg-primary-50',
            'day_off' => 'text-neutral-600 bg-neutral-200',
            default => 'text-warning-600 bg-warning-100',
        };
        $minutesToHours = fn (?int $minutes) => $minutes ? number_format($minutes / 60, 1) : '0.0';
    @endphp

    @if ($mode === 'summary')
        <div class="card radius-12">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('app.field_employee') }}</th>
                            <th>{{ __('app.status_present') }}</th>
                            <th>{{ __('app.status_absent') }}</th>
                            <th>{{ __('app.status_on_leave') }}</th>
                            <th>{{ __('app.status_remote') }}</th>
                            <th>{{ __('app.field_total_hours') }}</th>
                            <th>{{ __('app.field_avg_daily_hours') }}</th>
                            <th>{{ __('app.field_overtime') }}</th>
                            <th>{{ __('app.field_shortfall') }}</th>
                            <th>{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($summary as $row)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $row['user']?->avatar_url }}" alt="" class="w-24-px h-24-px rounded-circle object-fit-cover">
                                        {{ $row['user']?->name }}
                                    </div>
                                </td>
                                <td>{{ $row['present_days'] }}</td>
                                <td>{{ $row['absent_days'] }}</td>
                                <td>{{ $row['on_leave_days'] }}</td>
                                <td>{{ $row['remote_days'] }}</td>
                                <td>{{ $minutesToHours($row['total_minutes']) }}</td>
                                <td>{{ $row['avg_minutes'] ? $minutesToHours($row['avg_minutes']) : '—' }}</td>
                                <td class="text-success-600">{{ $minutesToHours($row['total_overtime_minutes']) }}</td>
                                <td class="text-danger-600">{{ $minutesToHours($row['total_shortfall_minutes']) }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <a href="{{ route('admin.attendance-report.index', array_merge(request()->except('user_id'), ['user_id' => $row['user']?->id])) }}" class="text-primary-600">
                                            {{ __('app.view_details') }}
                                        </a>
                                        <a href="{{ route('admin.attendance-report.excel', array_merge(request()->query(), ['date_from' => $dateFrom, 'date_to' => $dateTo, 'user_id' => $row['user']?->id])) }}" class="text-success-600 text-xl" title="{{ __('app.attendance_excel_employee') }}" aria-label="{{ __('app.attendance_excel_employee') }}">
                                            <i class="ri-file-excel-2-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card radius-12 mb-24">
            <div class="card-body d-flex align-items-center gap-3">
                <img src="{{ $employee?->avatar_url }}" alt="" class="w-48-px h-48-px rounded-circle object-fit-cover">
                <div>
                    <div class="fw-semibold">{{ $employee?->name }}</div>
                    <div class="text-secondary-light text-sm">{{ $employee?->employee_number }}</div>
                </div>
            </div>
        </div>

        <div class="card radius-12">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('app.log_date') }}</th>
                            <th>{{ __('app.field_status') }}</th>
                            <th>{{ __('app.field_check_in') }}</th>
                            <th>{{ __('app.field_check_out') }}</th>
                            <th>{{ __('app.field_duration') }}</th>
                            <th>{{ __('app.field_overtime') }}</th>
                            <th>{{ __('app.field_shortfall') }}</th>
                            @can('correct-attendance')
                                <th class="text-end">{{ __('app.actions') }}</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>{{ $row['date'] }}</td>
                                <td>
                                    <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $statusBadge($row['status']) }}">
                                        {{ __('app.status_'.$row['status']) }}
                                    </span>
                                    @if ($row['correction']?->reason)
                                        <div class="text-secondary-light text-xs mt-4">{{ $row['correction']->reason }}</div>
                                    @endif
                                </td>
                                <td>{{ $row['check_in']?->format('H:i:s') ?? '—' }}</td>
                                <td>{{ $row['check_out']?->format('H:i:s') ?? '—' }}</td>
                                <td>
                                    @if ($row['worked_minutes'] !== null)
                                        {{ sprintf('%d:%02d', intdiv($row['worked_minutes'], 60), $row['worked_minutes'] % 60) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-success-600">{{ $row['overtime_minutes'] ? $minutesToHours($row['overtime_minutes']) : '—' }}</td>
                                <td class="text-danger-600">{{ $row['shortfall_minutes'] ? $minutesToHours($row['shortfall_minutes']) : '—' }}</td>
                                @can('correct-attendance')
                                    <td class="text-end text-nowrap">
                                        @if ($row['date'] <= today()->toDateString())
                                            <button type="button" class="btn btn-outline-secondary-600 radius-8 px-12 py-6 text-sm"
                                                data-bs-toggle="modal" data-bs-target="#attendance-correction-modal"
                                                data-date="{{ $row['date'] }}"
                                                data-check-in="{{ $row['check_in']?->format('H:i') }}"
                                                data-check-out="{{ $row['check_out']?->format('H:i') }}"
                                                data-reason="{{ $row['correction']?->reason }}">
                                                <i class="ri-edit-2-line"></i> {{ __('app.edit') }}
                                            </button>
                                        @endif
                                        @if ($row['correction'])
                                            <form action="{{ route('admin.attendance-corrections.destroy', $row['correction']) }}" method="POST" class="d-inline" onsubmit="return confirm(@js(__('app.attendance_confirm_restore')));">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="btn btn-outline-danger-600 radius-8 px-12 py-6 text-sm">{{ __('app.attendance_restore') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @can('correct-attendance')
            <div class="modal fade" id="attendance-correction-modal" tabindex="-1">
                <div class="modal-dialog">
                    <form method="POST" action="{{ route('admin.attendance-corrections.store') }}" class="modal-content">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $employee?->id }}">
                        <input type="hidden" name="date" id="correction-date">
                        <div class="modal-header">
                            <h6 class="modal-title">{{ __('app.attendance_correct_title') }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-secondary-light text-sm mb-16">{{ $employee?->name }} — <span id="correction-date-label"></span></p>
                            <div class="row g-3">
                                <div class="col-6">
                                    <x-input-label for="correction-check-in" :value="__('app.field_check_in')" />
                                    <input type="time" id="correction-check-in" name="check_in" class="form-control mt-1">
                                </div>
                                <div class="col-6">
                                    <x-input-label for="correction-check-out" :value="__('app.field_check_out')" />
                                    <input type="time" id="correction-check-out" name="check_out" class="form-control mt-1">
                                </div>
                                <div class="col-12">
                                    <x-input-label for="correction-reason" :value="__('app.attendance_correct_reason')" />
                                    <input type="text" id="correction-reason" name="reason" maxlength="500" class="form-control mt-1">
                                </div>
                            </div>
                            <p class="text-secondary-light text-xs mt-16 mb-0">{{ __('app.attendance_correct_hint') }}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                            <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('app.save') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            @push('scripts')
                <script>
                    document.getElementById('attendance-correction-modal').addEventListener('show.bs.modal', function (event) {
                        var trigger = event.relatedTarget;
                        document.getElementById('correction-date').value = trigger.getAttribute('data-date');
                        document.getElementById('correction-date-label').textContent = trigger.getAttribute('data-date');
                        document.getElementById('correction-check-in').value = trigger.getAttribute('data-check-in');
                        document.getElementById('correction-check-out').value = trigger.getAttribute('data-check-out');
                        document.getElementById('correction-reason').value = trigger.getAttribute('data-reason');
                    });
                </script>
            @endpush
        @endcan
    @endif

    @push('scripts')
        <script src="{{ asset('assets/wowdash/js/lib/apexcharts.min.js') }}"></script>
        <script>
            (function () {
                const chartData = @json($chartData);

                if (!chartData.labels.length) {
                    return;
                }

                new ApexCharts(document.querySelector('#attendanceChart'), {
                    chart: { type: 'bar', height: 320, toolbar: { show: false } },
                    series: [{ name: @json(__('app.field_total_hours')), data: chartData.values }],
                    xaxis: { categories: chartData.labels },
                    dataLabels: { enabled: false },
                    colors: ['#487FFF'],
                }).render();
            })();
        </script>
    @endpush
</x-app-layout>
