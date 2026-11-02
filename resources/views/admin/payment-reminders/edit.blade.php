<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.edit') }} — {{ $reminder->title }}</h2>
    </x-slot>

    @include('admin.payment-reminders._status')

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.payment-reminders.update', $reminder) }}">
                @csrf
                @method('put')
                @include('admin.payment-reminders._form')
            </form>
        </div>
    </div>

    <div class="card radius-12">
        <div class="card-header bg-base fw-semibold d-flex align-items-center gap-2">
            <i class="ri-history-line text-primary-600"></i> {{ __('payments.history_title') }}
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('payments.field_due_date') }}</th>
                        <th>{{ __('payments.field_paid_on') }}</th>
                        <th>{{ __('payments.field_amount') }}</th>
                        <th>{{ __('payments.field_paid_by') }}</th>
                        <th>{{ __('payments.field_note') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->due_date->toDateString() }}</td>
                            <td>{{ $payment->paid_on->toDateString() }}</td>
                            <td>{{ $payment->amount !== null ? number_format((float) $payment->amount, 2).' '.__('payments.currency_'.$payment->currency) : '—' }}</td>
                            <td>{{ $payment->paidBy?->name ?? '—' }}</td>
                            <td>{{ $payment->note ?? '—' }}</td>
                            <td class="text-end">
                                @if ($loop->first)
                                    <form action="{{ route('admin.payment-reminders.payments.undo', [$reminder, $payment]) }}" method="POST" class="d-inline" onsubmit="return confirm(@js(__('payments.confirm_undo')));">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('payments.action_undo') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">{{ __('payments.history_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
