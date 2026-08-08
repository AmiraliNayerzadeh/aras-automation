<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.inbox_title') }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.field_name') }}</th>
                        <th>{{ __('app.field_status') }}</th>
                        <th>{{ __('leaves.field_from_date') }} — {{ __('leaves.field_to_date') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($steps as $step)
                        @php
                            $subject = $step->subject;
                            $isLeave = $subject instanceof \App\Models\Hr\LeaveRequest;
                            $prefix = $isLeave ? 'leaves' : 'missions';
                            $route = $isLeave
                                ? route('leave-requests.show', $subject)
                                : route('mission-requests.show', $subject);
                            $label = $isLeave
                                ? __('leaves.title_request_detail')
                                : __('missions.title_request_detail');
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $subject->user->avatar_url }}" alt="" class="w-32-px h-32-px rounded-circle object-fit-cover">
                                    <div>
                                        <div class="fw-medium">{{ $subject->user->name }}</div>
                                        <div class="text-secondary-light text-sm">{{ $label }} #{{ $subject->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge text-sm fw-semibold px-16 py-6 radius-4 text-warning-600 bg-warning-100">
                                    {{ __('app.step_'.$step->role->value) }}
                                </span>
                            </td>
                            <td>{{ $subject->from_date->format('Y-m-d') }} — {{ $subject->to_date->format('Y-m-d') }}</td>
                            <td class="text-end">
                                <a href="{{ $route }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('app.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('app.inbox_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
