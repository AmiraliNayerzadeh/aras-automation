<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('missions.title_my_requests') }}</h2>
            <a href="{{ route('mission-requests.create') }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">
                <i class="ri-add-line"></i> {{ __('missions.title_new_request') }}
            </a>
        </div>
    </x-slot>

    <div class="card radius-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('missions.field_mission_type') }}</th>
                        @can('missions.view_all')
                            <th>{{ __('app.field_name') }}</th>
                        @endcan
                        <th>{{ __('missions.field_destination') }}</th>
                        <th>{{ __('missions.field_from_date') }}</th>
                        <th>{{ __('missions.field_to_date') }}</th>
                        <th>{{ __('app.field_status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($missionRequests as $missionRequest)
                        @php
                            $statusColor = match ($missionRequest->status->value) {
                                'approved' => 'text-success-600 bg-success-100',
                                'rejected' => 'text-danger-600 bg-danger-100',
                                'pending', 'submitted' => 'text-warning-600 bg-warning-100',
                                default => 'text-neutral-600 bg-neutral-200',
                            };
                        @endphp
                        <tr>
                            <td>{{ $missionRequest->missionType->label[app()->getLocale()] ?? $missionRequest->missionType->code }}</td>
                            @can('missions.view_all')
                                <td>{{ $missionRequest->user->name }}</td>
                            @endcan
                            <td>{{ $missionRequest->destination }}</td>
                            <td>{{ $missionRequest->from_date->format('Y-m-d') }}</td>
                            <td>{{ $missionRequest->to_date->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $statusColor }}">
                                    {{ __('app.request_status_'.$missionRequest->status->value) }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('mission-requests.show', $missionRequest) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-body">
            {{ $missionRequests->links() }}
        </div>
    </div>
</x-app-layout>
