<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.title_work_shifts') }}</h2>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('app.flash_work_shift_updated') }}</div>
    @endif

    <div class="card radius-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.field_employee') }}</th>
                        <th>{{ __('app.field_schedule_type') }}</th>
                        <th>{{ __('app.field_remote') }}</th>
                        <th>{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $user->avatar_url }}" alt="" class="w-24-px h-24-px rounded-circle object-fit-cover">
                                    {{ $user->name }}
                                </div>
                            </td>
                            <td>
                                @if ($user->work_shifts_count > 0)
                                    <span class="badge text-sm fw-semibold px-16 py-6 radius-4 text-info-600 bg-info-100">{{ __('app.schedule_custom') }}</span>
                                @else
                                    <span class="badge text-sm fw-semibold px-16 py-6 radius-4 text-neutral-600 bg-neutral-200">{{ __('app.schedule_default') }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($user->is_remote)
                                    <span class="badge text-sm fw-semibold px-16 py-6 radius-4 text-primary-600 bg-primary-50">{{ __('app.status_remote') }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.work-shifts.edit', $user) }}" class="text-primary-600">{{ __('app.edit') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
