@if (($withDate ?? true) || ($withUser ?? true))
    <div style="font-size: 11px; color: #777; margin-bottom: 10px;">
        @if ($withDate ?? true)
            {{ __('exports.generated_on', ['date' => now()->format('Y-m-d H:i')]) }}
        @endif
        @if ($withUser ?? true)
            {{ __('exports.generated_by', ['name' => auth()->user()->name]) }}
        @endif
    </div>
@endif
