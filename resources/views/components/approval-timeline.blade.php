@props(['steps'])

<div>
    @foreach ($steps as $step)
        @php
            $color = match ($step->status->value) {
                'approved' => 'success',
                'rejected' => 'danger',
                'skipped' => 'neutral',
                default => 'warning',
            };
            $icon = match ($step->status->value) {
                'approved' => 'ri-check-line',
                'rejected' => 'ri-close-line',
                'skipped' => 'ri-skip-forward-line',
                default => 'ri-time-line',
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
                    <h6 class="mb-0">{{ __('app.step_'.$step->role->value) }}</h6>
                    <span class="badge text-sm fw-semibold px-12 py-4 radius-4 text-{{ $color }}-600 bg-{{ $color }}-100">
                        {{ __('app.step_status_'.$step->status->value) }}
                    </span>
                </div>

                @if ($step->approver)
                    <p class="text-secondary-light text-sm mb-4">{{ $step->approver->name }}</p>
                @endif

                @if ($step->acted_by_id && $step->actedBy)
                    <p class="text-secondary-light text-sm mb-4">
                        {{ __('leaves.timeline_acted_by', ['name' => $step->actedBy->name]) }}
                        — {{ $step->acted_at?->format('Y-m-d H:i') }}
                    </p>
                @endif

                @if ($step->comment)
                    <p class="text-sm mb-0">{{ $step->comment }}</p>
                @elseif ($step->system_note)
                    <p class="text-secondary-light text-sm mb-0 fst-italic">{{ $step->system_note }}</p>
                @endif
            </div>
        </div>
    @endforeach
</div>
