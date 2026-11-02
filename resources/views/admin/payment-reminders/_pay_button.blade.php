<button type="button" class="btn btn-success-600 radius-8 px-16 py-8 text-sm"
    data-bs-toggle="modal" data-bs-target="#payment-pay-modal"
    data-action="{{ route('admin.payment-reminders.pay', $reminder) }}"
    data-title="{{ $reminder->title }}"
    data-due-date="{{ $reminder->next_due_date->toDateString() }}"
    data-amount="{{ $reminder->amount }}">
    <i class="ri-check-line"></i> {{ __('payments.action_mark_paid') }}
</button>
