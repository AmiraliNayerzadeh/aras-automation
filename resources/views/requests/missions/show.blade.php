@php
    $statusColor = match ($missionRequest->status->value) {
        'approved' => 'text-success-600 bg-success-100',
        'rejected' => 'text-danger-600 bg-danger-100',
        'pending', 'submitted' => 'text-warning-600 bg-warning-100',
        default => 'text-neutral-600 bg-neutral-200',
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('missions.title_request_detail') }} #{{ $missionRequest->id }}</h2>
            <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $statusColor }}">
                {{ __('app.request_status_'.$missionRequest->status->value) }}
            </span>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('missions.flash_'.str_replace('mission-', '', session('status'))) }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card radius-12 mb-24">
                <div class="card-header bg-base fw-semibold">{{ __('missions.title_request_detail') }}</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('missions.field_mission_type') }}</dt>
                        <dd class="col-sm-8">{{ $missionRequest->missionType->label[app()->getLocale()] ?? $missionRequest->missionType->code }}</dd>

                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('app.field_name') }}</dt>
                        <dd class="col-sm-8">{{ $missionRequest->user->name }}</dd>

                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('missions.field_destination') }}</dt>
                        <dd class="col-sm-8">{{ $missionRequest->destination }}</dd>

                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('missions.field_from_date') }}</dt>
                        <dd class="col-sm-8">{{ $missionRequest->from_date->format('Y-m-d') }}</dd>

                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('missions.field_to_date') }}</dt>
                        <dd class="col-sm-8">{{ $missionRequest->to_date->format('Y-m-d') }}</dd>

                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('missions.field_purpose') }}</dt>
                        <dd class="col-sm-8">{{ $missionRequest->purpose }}</dd>

                        @if ($missionRequest->outbound_transport)
                            <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('missions.field_outbound_transport') }}</dt>
                            <dd class="col-sm-8">{{ $missionRequest->outbound_transport }}</dd>
                        @endif

                        @if ($missionRequest->return_transport)
                            <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('missions.field_return_transport') }}</dt>
                            <dd class="col-sm-8">{{ $missionRequest->return_transport }}</dd>
                        @endif

                        @if ($missionRequest->estimated_cost)
                            <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('missions.field_estimated_cost') }}</dt>
                            <dd class="col-sm-8">{{ $missionRequest->estimated_cost }} {{ $missionRequest->currency }}</dd>
                        @endif

                        @if ($missionRequest->actual_cost)
                            <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('missions.field_actual_cost') }}</dt>
                            <dd class="col-sm-8">{{ $missionRequest->actual_cost }} {{ $missionRequest->currency }}</dd>
                        @endif

                        @if ($missionRequest->mission_report)
                            <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('missions.field_mission_report') }}</dt>
                            <dd class="col-sm-8">{{ $missionRequest->mission_report }}</dd>
                        @endif

                        @if ($missionRequest->attachments->isNotEmpty())
                            <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('missions.field_receipt') }}</dt>
                            <dd class="col-sm-8">
                                @foreach ($missionRequest->attachments as $attachment)
                                    <a href="{{ asset('storage/'.$attachment->file_path) }}" target="_blank" class="d-block">
                                        <i class="ri-attachment-2"></i> {{ $attachment->original_name }}
                                    </a>
                                @endforeach
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>

            @can('report', $missionRequest)
                <div class="card radius-12 mb-24">
                    <div class="card-header bg-base fw-semibold">{{ __('missions.action_add_report') }}</div>
                    <div class="card-body">
                        <p class="text-secondary-light text-sm">{{ __('missions.report_available_hint') }}</p>
                        <form method="POST" action="{{ route('mission-requests.report', $missionRequest) }}">
                            @csrf
                            @method('put')
                            <div class="mb-3">
                                <x-input-label for="actual_cost" :value="__('missions.field_actual_cost')" />
                                <x-text-input id="actual_cost" name="actual_cost" type="number" step="0.01" min="0" class="mt-1 w-100" />
                                <x-input-error :messages="$errors->get('actual_cost')" class="mt-1" />
                            </div>
                            <div class="mb-3">
                                <x-input-label for="mission_report" :value="__('missions.field_mission_report')" />
                                <textarea id="mission_report" name="mission_report" rows="4" class="form-control radius-8 mt-1" required></textarea>
                                <x-input-error :messages="$errors->get('mission_report')" class="mt-1" />
                            </div>
                            <x-primary-button>{{ __('missions.action_add_report') }}</x-primary-button>
                        </form>
                    </div>
                </div>
            @endcan

            @can('cancel', $missionRequest)
                <form method="POST" action="{{ route('mission-requests.cancel', $missionRequest) }}" onsubmit="return confirm('{{ __('missions.confirm_cancel') }}');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger-600 radius-8 px-16 py-8 text-sm">
                        <i class="ri-close-circle-line"></i> {{ __('missions.action_cancel') }}
                    </button>
                </form>
            @endcan
        </div>

        <div class="col-lg-5">
            <div class="card radius-12">
                <div class="card-header bg-base fw-semibold">{{ __('missions.timeline_title') }}</div>
                <div class="card-body">
                    <x-approval-timeline :steps="$missionRequest->approvalSteps" />

                    @php
                        $currentStep = $missionRequest->currentStep();
                    @endphp

                    @if ($currentStep && (auth()->user()->is($currentStep->approver) || (! $currentStep->approver_id && auth()->user()->hasAnyRole(['hr', 'admin', 'super-admin']))))
                        <hr class="my-16">
                        <form method="POST" action="{{ route('approvals.approve', $currentStep) }}" class="mb-8">
                            @csrf
                            <textarea name="comment" class="form-control radius-8 mb-8" rows="2" placeholder="{{ __('missions.timeline_comment_placeholder') }}"></textarea>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success-600 radius-8 px-16 py-8 text-sm">
                                    <i class="ri-check-line"></i> {{ __('missions.action_approve') }}
                                </button>
                                <button type="submit" formaction="{{ route('approvals.reject', $currentStep) }}" class="btn btn-outline-danger-600 radius-8 px-16 py-8 text-sm">
                                    <i class="ri-close-line"></i> {{ __('missions.action_reject') }}
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
