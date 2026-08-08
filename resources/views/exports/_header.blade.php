@if ($withHeader ?? true)
    <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 12px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <img src="{{ $logoSrc }}" alt="" style="height: 42px;">
            <strong style="font-size: 16px;">{{ config('app.name') }}</strong>
        </div>
        <div style="font-size: 15px; font-weight: bold;">{{ $title }}</div>
    </div>
@endif
