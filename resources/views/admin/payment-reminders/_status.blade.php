@if (session('status') && str_contains(session('status'), 'reminder') || in_array(session('status'), ['payment-recorded', 'payment-already-recorded', 'payment-undone', 'payment-undo-refused'], true))
    <div class="alert {{ in_array(session('status'), ['payment-already-recorded', 'payment-undo-refused'], true) ? 'alert-warning' : 'alert-success' }} radius-8 mb-24">
        {{ __('payments.flash_'.str_replace('-', '_', session('status'))) }}
    </div>
@endif
