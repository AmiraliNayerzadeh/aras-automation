<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.title_face_device_events') }}</h2>
    </x-slot>

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
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
                    <x-input-label for="verify_mode" :value="__('app.field_verify_mode')" />
                    <select id="verify_mode" name="verify_mode" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($verifyModes as $mode)
                            <option value="{{ $mode }}" @selected(request('verify_mode') === $mode)>{{ $mode }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <x-input-label for="date_from" :value="__('orders.field_date_from')" />
                    <x-text-input id="date_from" name="date_from" type="date" class="mt-1 w-100" :value="request('date_from')" />
                </div>
                <div class="col-md-2">
                    <x-input-label for="date_to" :value="__('orders.field_date_to')" />
                    <x-text-input id="date_to" name="date_to" type="date" class="mt-1 w-100" :value="request('date_to')" />
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary-600 radius-8 w-100 py-8">
                        <i class="ri-filter-3-line"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card radius-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.field_event_time') }}</th>
                        <th>{{ __('app.field_employee') }}</th>
                        <th>{{ __('app.field_employee_no') }}</th>
                        <th>{{ __('app.field_verify_mode') }}</th>
                        <th>{{ __('app.field_event_type') }}</th>
                        <th>{{ __('app.field_attendance_status') }}</th>
                        <th>{{ __('app.field_device_serial') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($events as $event)
                        <tr>
                            <td class="text-nowrap">{{ $event->event_time?->format('Y-m-d H:i:s') }}</td>
                            <td>
                                @if ($event->user)
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $event->user->avatar_url }}" alt="" class="w-24-px h-24-px rounded-circle object-fit-cover">
                                        {{ $event->user->name }}
                                    </div>
                                @else
                                    <span class="badge text-sm fw-semibold px-16 py-6 radius-4 text-warning-600 bg-warning-100">
                                        {{ __('app.unmatched_employee') }}
                                    </span>
                                @endif
                            </td>
                            <td>{{ $event->employee_no ?? '—' }}</td>
                            <td>{{ $event->verify_mode ?? '—' }}</td>
                            <td>{{ $event->minor_event ?? $event->major_event ?? '—' }}</td>
                            <td>{{ $event->attendance_status ?? '—' }}</td>
                            <td>{{ $event->device_serial ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-body">
            {{ $events->links() }}
        </div>
    </div>
</x-app-layout>
