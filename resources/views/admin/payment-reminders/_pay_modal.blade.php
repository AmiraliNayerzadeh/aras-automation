{{-- Shared, JS-populated from the _pay_button that opened it. --}}
<div class="modal fade" id="payment-pay-modal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="" id="payment-pay-form" class="modal-content">
            @csrf
            <input type="hidden" name="due_date" id="payment-pay-due-date">
            <div class="modal-header">
                <h6 class="modal-title">{{ __('payments.action_mark_paid') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary-light text-sm mb-16" id="payment-pay-summary"></p>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <x-input-label for="payment-pay-paid-on" :value="__('payments.field_paid_on')" />
                        <input type="date" id="payment-pay-paid-on" name="paid_on" class="form-control mt-1" max="{{ today()->toDateString() }}" value="{{ today()->toDateString() }}" required>
                    </div>
                    <div class="col-sm-6">
                        <x-input-label for="payment-pay-amount" :value="__('payments.field_amount')" />
                        <input type="number" step="0.01" min="0" id="payment-pay-amount" name="amount" class="form-control mt-1">
                    </div>
                    <div class="col-12">
                        <x-input-label for="payment-pay-note" :value="__('payments.field_note')" />
                        <input type="text" id="payment-pay-note" name="note" maxlength="500" class="form-control mt-1">
                    </div>
                </div>
                <p class="text-secondary-light text-xs mt-16 mb-0">{{ __('payments.pay_hint') }}</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                <button type="submit" class="btn btn-success-600 radius-8 px-16 py-8 text-sm">{{ __('payments.action_mark_paid') }}</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        document.getElementById('payment-pay-modal').addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            document.getElementById('payment-pay-form').action = trigger.getAttribute('data-action');
            document.getElementById('payment-pay-due-date').value = trigger.getAttribute('data-due-date');
            document.getElementById('payment-pay-amount').value = trigger.getAttribute('data-amount');
            document.getElementById('payment-pay-note').value = '';
            document.getElementById('payment-pay-summary').textContent = trigger.getAttribute('data-title') + ' — ' + trigger.getAttribute('data-due-date');
        });
    </script>
@endpush
