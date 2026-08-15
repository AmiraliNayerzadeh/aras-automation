<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.title_edit_work_shift', ['name' => $employee->name]) }}</h2>
    </x-slot>

    <form method="POST" action="{{ route('admin.work-shifts.update', $employee) }}">
        @csrf
        @method('PUT')

        <div class="card radius-12 mb-24">
            <div class="card-body">
                <div class="form-check form-switch">
                    <input type="checkbox" id="is_remote" name="is_remote" value="1" class="form-check-input" @checked($employee->is_remote)>
                    <label for="is_remote" class="form-check-label">{{ __('app.field_remote') }}</label>
                </div>
                <div class="text-secondary-light text-sm mt-1">{{ __('app.field_remote_hint') }}</div>
            </div>
        </div>

        <div class="card radius-12">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 160px">{{ __('app.field_day') }}</th>
                            <th style="width: 120px">{{ __('app.field_day_off') }}</th>
                            <th>{{ __('app.field_check_in') }}</th>
                            <th>{{ __('app.field_check_out') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (range(0, 6) as $dayOfWeek)
                            @php $day = $schedule[$dayOfWeek]; @endphp
                            <tr>
                                <td class="fw-medium">{{ __('app.day_'.$dayOfWeek) }}</td>
                                <td>
                                    <div class="form-check">
                                        <input
                                            type="checkbox"
                                            id="day_off_{{ $dayOfWeek }}"
                                            name="shifts[{{ $dayOfWeek }}][is_day_off]"
                                            value="1"
                                            class="form-check-input day-off-toggle"
                                            data-day="{{ $dayOfWeek }}"
                                            @checked($day['is_day_off'])
                                        >
                                    </div>
                                </td>
                                <td>
                                    <input
                                        type="time"
                                        name="shifts[{{ $dayOfWeek }}][start_time]"
                                        id="start_{{ $dayOfWeek }}"
                                        class="form-control radius-8"
                                        value="{{ $day['start_time'] ? \Illuminate\Support\Carbon::parse($day['start_time'])->format('H:i') : '' }}"
                                        @disabled($day['is_day_off'])
                                    >
                                </td>
                                <td>
                                    <input
                                        type="time"
                                        name="shifts[{{ $dayOfWeek }}][end_time]"
                                        id="end_{{ $dayOfWeek }}"
                                        class="form-control radius-8"
                                        value="{{ $day['end_time'] ? \Illuminate\Support\Carbon::parse($day['end_time'])->format('H:i') : '' }}"
                                        @disabled($day['is_day_off'])
                                    >
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-body d-flex justify-content-end gap-2">
                <a href="{{ route('admin.work-shifts.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
                <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('app.save_shift') }}</button>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            document.querySelectorAll('.day-off-toggle').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    var day = this.dataset.day;
                    var start = document.getElementById('start_' + day);
                    var end = document.getElementById('end_' + day);
                    start.disabled = end.disabled = this.checked;
                });
            });
        </script>
    @endpush
</x-app-layout>
