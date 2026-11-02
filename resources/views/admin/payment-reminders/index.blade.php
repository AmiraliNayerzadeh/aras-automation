<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('payments.title_index') }}</h2>
            <a href="{{ route('admin.payment-reminders.create') }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('app.create_new') }}</a>
        </div>
    </x-slot>

    @include('admin.payment-reminders._status')

    <div class="card radius-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('payments.field_title') }}</th>
                        <th>{{ __('payments.field_amount') }}</th>
                        <th>{{ __('payments.field_interval') }}</th>
                        <th>{{ __('payments.field_next_due_date') }}</th>
                        <th>{{ __('app.field_status') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reminders as $reminder)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $reminder->title }}</div>
                                @if ($reminder->notes)
                                    <div class="text-secondary-light text-xs">{{ \Illuminate\Support\Str::limit($reminder->notes, 80) }}</div>
                                @endif
                            </td>
                            <td>{{ $reminder->formattedAmount() ?? '—' }}</td>
                            <td>{{ __('payments.interval_'.$reminder->interval_months) }}</td>
                            <td>{{ $reminder->next_due_date->toDateString() }}</td>
                            <td>@include('admin.payment-reminders._badge')</td>
                            <td class="text-end text-nowrap">
                                @if ($reminder->is_active)
                                    @include('admin.payment-reminders._pay_button')
                                @endif
                                <a href="{{ route('admin.payment-reminders.edit', $reminder) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.edit') }}</a>
                                <form action="{{ route('admin.payment-reminders.destroy', $reminder) }}" method="POST" class="d-inline" onsubmit="return confirm(@js(__('app.confirm_delete')));">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-outline-danger-600 radius-8 px-16 py-8 text-sm">{{ __('app.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('admin.payment-reminders._pay_modal')
</x-app-layout>
