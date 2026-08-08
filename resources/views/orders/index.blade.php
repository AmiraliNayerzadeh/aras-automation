<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ __('orders.title_index') }}</h2>
            @can('orders.create')
                <a href="{{ route('orders.create') }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-add-line"></i> {{ __('orders.title_create') }}
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <x-input-label for="type" :value="__('orders.field_type')" />
                    <select id="type" name="type" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        <option value="internal" @selected(request('type') === 'internal')>{{ __('orders.type_internal') }}</option>
                        <option value="external" @selected(request('type') === 'external')>{{ __('orders.type_external') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <x-input-label for="stage" :value="__('orders.field_stage')" />
                    <select id="stage" name="stage" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($stages as $stage)
                            <option value="{{ $stage->id }}" @selected(request('stage') == $stage->id)>{{ $stage->label[app()->getLocale()] ?? $stage->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <x-input-label for="business_partner_id" :value="__('orders.field_business_partner')" />
                    <select id="business_partner_id" name="business_partner_id" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($partners as $partner)
                            <option value="{{ $partner->id }}" @selected(request('business_partner_id') == $partner->id)>{{ $partner->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <x-input-label for="date_from" :value="__('orders.field_date_from')" />
                    <x-text-input id="date_from" name="date_from" type="date" class="mt-1 w-100" :value="request('date_from')" />
                </div>
                <div class="col-md-2">
                    <x-input-label for="date_to" :value="__('orders.field_date_to')" />
                    <x-text-input id="date_to" name="date_to" type="date" class="mt-1 w-100" :value="request('date_to')" />
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">
                        <i class="ri-filter-3-line"></i> {{ __('app.actions') }}
                    </button>
                    <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card radius-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('orders.field_order_number') }}</th>
                        <th>{{ __('orders.field_type') }}</th>
                        <th>{{ __('orders.field_business_partner') }}</th>
                        <th>{{ __('orders.field_order_date') }}</th>
                        <th>{{ __('orders.field_amount') }}</th>
                        <th>{{ __('orders.field_stage') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        @php($stageColor = $order->currentStage?->color ?? 'neutral')
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ __('orders.type_'.$order->type->value) }}</td>
                            <td>{{ $order->businessPartner->name }}</td>
                            <td>{{ $order->order_date->format('Y-m-d') }}</td>
                            <td>{{ number_format((float) $order->amount, 2) }} {{ $order->currency }}</td>
                            <td>
                                <span class="badge text-sm fw-semibold px-16 py-6 radius-4 text-{{ $stageColor }}-600 bg-{{ $stageColor }}-100">
                                    {{ $order->currentStage?->label[app()->getLocale()] ?? '—' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.view') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-24">
        {{ $orders->links() }}
    </div>
</x-app-layout>
