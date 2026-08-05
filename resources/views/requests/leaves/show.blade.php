@php
    $statusColor = match ($leaveRequest->status->value) {
        'approved' => 'text-success-600 bg-success-100',
        'rejected' => 'text-danger-600 bg-danger-100',
        'pending', 'submitted' => 'text-warning-600 bg-warning-100',
        default => 'text-neutral-600 bg-neutral-200',
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('leaves.title_request_detail') }} #{{ $leaveRequest->id }}</h2>
            <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $statusColor }}">
                {{ __('app.request_status_'.$leaveRequest->status->value) }}
            </span>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('leaves.flash_'.str_replace('leave-', '', session('status'))) }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card radius-12 mb-24">
                <div class="card-header bg-base fw-semibold">{{ __('leaves.title_request_detail') }}</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('leaves.field_leave_type') }}</dt>
                        <dd class="col-sm-8">{{ $leaveRequest->leaveType->label[app()->getLocale()] ?? $leaveRequest->leaveType->code }}</dd>

                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('app.field_name') }}</dt>
                        <dd class="col-sm-8">{{ $leaveRequest->user->name }}</dd>

                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('leaves.field_from_date') }}</dt>
                        <dd class="col-sm-8">{{ $leaveRequest->from_date->format('Y-m-d') }}</dd>

                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('leaves.field_to_date') }}</dt>
                        <dd class="col-sm-8">{{ $leaveRequest->to_date->format('Y-m-d') }}</dd>

                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('leaves.field_day_count') }}</dt>
                        <dd class="col-sm-8">{{ $leaveRequest->day_count }}</dd>

                        @if ($leaveRequest->substitute)
                            <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('leaves.field_substitute') }}</dt>
                            <dd class="col-sm-8">{{ $leaveRequest->substitute->name }}</dd>
                        @endif

                        @if ($leaveRequest->description)
                            <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('leaves.field_description') }}</dt>
                            <dd class="col-sm-8">{{ $leaveRequest->description }}</dd>
                        @endif

                        @if ($leaveRequest->attachments->isNotEmpty())
                            <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('leaves.field_attachment') }}</dt>
                            <dd class="col-sm-8">
                                @foreach ($leaveRequest->attachments as $attachment)
                                    <a href="{{ asset('storage/'.$attachment->file_path) }}" target="_blank" class="d-block">
                                        <i class="ri-attachment-2"></i> {{ $attachment->original_name }}
                                    </a>
                                @endforeach
                            </dd>
                        @endif
                    </dl>
                </div>
            </div>

            @can('cancel', $leaveRequest)
                <form method="POST" action="{{ route('leave-requests.cancel', $leaveRequest) }}" onsubmit="return confirm('{{ __('leaves.confirm_cancel') }}');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger-600 radius-8 px-16 py-8 text-sm">
                        <i class="ri-close-circle-line"></i> {{ __('leaves.action_cancel') }}
                    </button>
                </form>
            @endcan
        </div>

        <div class="col-lg-5">
            <div class="card radius-12">
                <div class="card-header bg-base fw-semibold">{{ __('leaves.timeline_title') }}</div>
                <div class="card-body">
                    <x-approval-timeline :steps="$leaveRequest->approvalSteps" />

                    @php
                        $currentStep = $leaveRequest->currentStep();
                    @endphp

                    @if ($currentStep && (auth()->user()->is($currentStep->approver) || (! $currentStep->approver_id && auth()->user()->hasAnyRole(['hr', 'admin', 'super-admin']))))
                        <hr class="my-16">
                        <form method="POST" action="{{ route('approvals.approve', $currentStep) }}" class="mb-8">
                            @csrf
                            <textarea name="comment" class="form-control radius-8 mb-8" rows="2" placeholder="{{ __('leaves.timeline_comment_placeholder') }}"></textarea>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success-600 radius-8 px-16 py-8 text-sm">
                                    <i class="ri-check-line"></i> {{ __('leaves.action_approve') }}
                                </button>
                                <button type="submit" formaction="{{ route('approvals.reject', $currentStep) }}" class="btn btn-outline-danger-600 radius-8 px-16 py-8 text-sm">
                                    <i class="ri-close-line"></i> {{ __('leaves.action_reject') }}
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
