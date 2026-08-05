<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.nav_activity_log') }}</h2>
    </x-slot>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.log_date') }}</th>
                        <th>{{ __('app.log_causer') }}</th>
                        <th>{{ __('app.log_event') }}</th>
                        <th>{{ __('app.log_subject') }}</th>
                        <th>{{ __('app.field_name') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr>
                            <td class="text-nowrap">{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $activity->causer?->name ?? '—' }}</td>
                            <td><span class="badge text-bg-light border">{{ $activity->event }}</span></td>
                            <td>{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</td>
                            <td>{{ $activity->description }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-body">
            {{ $activities->links() }}
        </div>
    </div>
</x-app-layout>
