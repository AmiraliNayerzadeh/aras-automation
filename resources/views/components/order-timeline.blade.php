@props(['order', 'stages'])

@php
    $logsByStage = $order->stageLogs->keyBy('lookup_value_id');
    $currentStageId = $order->current_stage_lookup_value_id;
@endphp

<div>
    @foreach ($stages as $stage)
        @php
            $log = $logsByStage->get($stage->id);
            $isCurrent = $stage->id === $currentStageId && ! $order->is_closed;
            $state = match (true) {
                $log && $log->is_skipped => 'skipped',
                $isCurrent => 'current',
                $log => 'done',
                default => 'pending',
            };
            $color = match ($state) {
                'done' => 'success',
                'current' => 'warning',
                'skipped' => 'neutral',
                default => 'neutral',
            };
            $icon = match ($state) {
                'done' => 'ri-check-line',
                'current' => 'ri-time-line',
                'skipped' => 'ri-skip-forward-line',
                default => 'ri-more-line',
            };
        @endphp
        <div class="d-flex gap-16 position-relative pb-24">
            @unless ($loop->last)
                <span class="position-absolute top-0 bottom-0 border-start border-2 border-{{ $color }}-200" style="inset-inline-start: 15px; margin-top: 32px;"></span>
            @endunless

            <div class="w-32-px h-32-px radius-8 bg-{{ $color }}-100 text-{{ $color }}-600 d-flex align-items-center justify-content-center flex-shrink-0 z-1">
                <i class="{{ $icon }}"></i>
            </div>

            <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-8 mb-4">
                    <h6 class="mb-0">{{ $stage->label[app()->getLocale()] ?? $stage->code }}</h6>
                    @if ($state === 'skipped')
                        <span class="badge text-sm fw-semibold px-12 py-4 radius-4 text-neutral-600 bg-neutral-200">{{ __('orders.timeline_skipped') }}</span>
                    @endif
                </div>

                @if ($log)
                    @if ($log->responsibleUser)
                        <p class="text-secondary-light text-sm mb-4 d-flex align-items-center gap-2">
                            <img src="{{ $log->responsibleUser->avatar_url }}" alt="" class="w-24-px h-24-px rounded-circle object-fit-cover">
                            {{ $log->responsibleUser->name }}
                        </p>
                    @endif

                    <p class="text-secondary-light text-sm mb-4">
                        {{ $log->occurred_at->format('Y-m-d H:i') }}
                        @if ($log->createdBy)
                            — {{ __('orders.timeline_recorded_by', ['name' => $log->createdBy->name]) }}
                        @endif
                    </p>

                    @if ($log->cost)
                        <p class="text-sm mb-4">{{ __('orders.field_cost') }}: {{ number_format((float) $log->cost, 2) }} {{ $order->currency }}</p>
                    @endif

                    @if ($log->description)
                        <p class="text-sm mb-4">{{ $log->description }}</p>
                    @endif

                    @if ($log->comment)
                        <p class="text-secondary-light text-sm mb-4 fst-italic">{{ $log->comment }}</p>
                    @endif

                    @if ($log->documents->isNotEmpty())
                        <div class="d-flex flex-column gap-1">
                            @foreach ($log->documents as $document)
                                <a href="{{ asset('storage/'.$document->file_path) }}" target="_blank" class="text-sm">
                                    <i class="ri-attachment-2"></i> {{ $document->original_name }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endforeach
</div>
