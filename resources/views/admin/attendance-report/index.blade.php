<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('app.title_attendance_report') }}</h2>
            @if ($mode === 'detail')
                <a href="{{ route('admin.attendance-report.index', request()->except('user_id')) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-arrow-go-back-line"></i> {{ __('app.back_to_summary') }}
                </a>
            @endif
        </div>
    </x-slot>

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

    @if ($mode === 'summary')
        <div class="card radius-12">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('app.field_employee') }}</th>
                            <th>{{ __('app.field_present_days') }}</th>
                            <th>{{ __('app.field_total_hours') }}</th>
                            <th>{{ __('app.field_avg_daily_hours') }}</th>
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
                                <td>{{ number_format($row['total_minutes'] / 60, 1) }}</td>
                                <td>{{ $row['avg_minutes'] ? number_format($row['avg_minutes'] / 60, 1) : '—' }}</td>
                                <td>
                                    <a href="{{ route('admin.attendance-report.index', array_merge(request()->except('user_id'), ['user_id' => $row['user']?->id])) }}" class="text-primary-600">
                                        {{ __('app.view_details') }}
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
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
                            <th>{{ __('app.field_check_in') }}</th>
                            <th>{{ __('app.field_check_out') }}</th>
                            <th>{{ __('app.field_duration') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>{{ $row['date'] }}</td>
                                <td>{{ $row['check_in']->format('H:i:s') }}</td>
                                <td>{{ $row['check_out']?->format('H:i:s') ?? '—' }}</td>
                                <td>
                                    @if ($row['duration_minutes'] !== null)
                                        {{ sprintf('%d:%02d', intdiv($row['duration_minutes'], 60), $row['duration_minutes'] % 60) }}
                                    @else
                                        <span class="badge text-sm fw-semibold px-16 py-6 radius-4 text-warning-600 bg-warning-100">
                                            {{ __('app.incomplete') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
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
