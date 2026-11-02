@php
    $days = $reminder->daysUntilDue();
    [$badgeClass, $badgeText] = match ($reminder->stage()) {
        'overdue' => ['text-danger-600 bg-danger-100', __('payments.stage_overdue', ['days' => abs($days)])],
        'today' => ['text-danger-600 bg-danger-100', __('payments.stage_today')],
        'soon' => ['text-warning-600 bg-warning-100', __('payments.stage_soon', ['days' => $days])],
        'inactive' => ['text-neutral-600 bg-neutral-200', __('app.field_inactive')],
        default => ['text-success-600 bg-success-100', __('payments.stage_upcoming', ['days' => $days])],
    };
@endphp
<span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $badgeClass }}">{{ $badgeText }}</span>
