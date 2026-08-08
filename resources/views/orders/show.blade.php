@php
    $stageColor = $order->currentStage?->color ?? 'neutral';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 fw-semibold mb-0">{{ $order->order_number }}</h2>
            <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $order->is_closed ? 'text-success-600 bg-success-100' : 'text-warning-600 bg-warning-100' }}">
                {{ $order->is_closed ? __('orders.status_closed') : __('orders.status_open') }}
            </span>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('orders.flash_'.str_replace('order-', '', session('status'))) }}</div>
    @endif

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card radius-12 mb-24">
                <div class="card-header bg-base fw-semibold d-flex justify-content-between align-items-center">
                    {{ __('orders.title_detail') }}
                    <div class="d-flex gap-2">
                        @can('orders.edit')
                            @if ($order->isEditable())
                                <a href="{{ route('orders.edit', $order) }}" class="btn btn-outline-secondary-600 btn-sm radius-8 px-12 py-6 text-sm">{{ __('app.edit') }}</a>
                            @endif
                        @endcan
                        @can('orders.delete')
                            <form action="{{ route('orders.destroy', $order) }}" method="POST" onsubmit="return confirm('{{ __('orders.confirm_delete') }}');">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-outline-danger-600 btn-sm radius-8 px-12 py-6 text-sm">{{ __('app.delete') }}</button>
                            </form>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('orders.field_type') }}</dt>
                        <dd class="col-sm-8">{{ __('orders.type_'.$order->type->value) }}</dd>

                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('orders.field_business_partner') }}</dt>
                        <dd class="col-sm-8">{{ $order->businessPartner->name }}</dd>

                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('orders.field_order_date') }}</dt>
                        <dd class="col-sm-8">{{ $order->order_date->format('Y-m-d') }}</dd>

                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('orders.field_amount') }}</dt>
                        <dd class="col-sm-8">{{ number_format((float) $order->amount, 2) }} {{ $order->currency }}</dd>

                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('orders.field_stage') }}</dt>
                        <dd class="col-sm-8">
                            <span class="badge text-sm fw-semibold px-16 py-6 radius-4 text-{{ $stageColor }}-600 bg-{{ $stageColor }}-100">
                                {{ $order->currentStage?->label[app()->getLocale()] ?? '—' }}
                            </span>
                        </dd>

                        @if ($order->description)
                            <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('orders.field_description') }}</dt>
                            <dd class="col-sm-8">{{ $order->description }}</dd>
                        @endif

                        <dt class="col-sm-4 text-secondary-light fw-medium">{{ __('app.field_name') }}</dt>
                        <dd class="col-sm-8 d-flex align-items-center gap-2">
                            <img src="{{ $order->createdBy?->avatar_url }}" alt="" class="w-24-px h-24-px rounded-circle object-fit-cover">
                            {{ $order->createdBy?->name }}
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card radius-12">
                <div class="card-header bg-base fw-semibold">{{ __('orders.items_title') }}</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>{{ __('orders.field_item_description') }}</th>
                                <th>{{ __('orders.field_quantity') }}</th>
                                <th>{{ __('orders.field_unit') }}</th>
                                <th>{{ __('orders.field_packaging') }}</th>
                                <th>{{ __('orders.field_unit_price') }}</th>
                                <th>{{ __('orders.field_line_total') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($order->items as $item)
                                <tr>
                                    <td>
                                        {{ $item->description }}
                                        @if ($item->product)
                                            <span class="badge text-xs fw-semibold px-8 py-2 radius-4 text-primary-600 bg-primary-100 ms-1" title="{{ __('orders.field_linked_product') }}">
                                                <i class="ri-barcode-line"></i> {{ $item->product->sku }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ rtrim(rtrim(number_format((float) $item->quantity, 3), '0'), '.') }}</td>
                                    <td>{{ $item->unit ?? '—' }}</td>
                                    <td>{{ $item->packaging ?? '—' }}</td>
                                    <td>{{ $item->unit_price !== null ? number_format((float) $item->unit_price, 2) : '—' }}</td>
                                    <td>{{ $item->line_total !== null ? number_format((float) $item->line_total, 2) : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">{{ __('orders.no_items') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @php($linkedItemsCount = $order->items->whereNotNull('product_id')->count())
            @can('stock.record')
                @if ($linkedItemsCount > 0)
                    <div class="card radius-12 mt-24">
                        <div class="card-header bg-base fw-semibold">{{ __('orders.title_post_stock') }}</div>
                        <div class="card-body">
                            @if ($stockAlreadyPosted)
                                <p class="text-success-600 mb-0"><i class="ri-checkbox-circle-line"></i> {{ __('orders.flash_stockposted') }}</p>
                            @else
                                @php($direction = $order->businessPartner->type === 'customer' ? __('orders.post_stock_direction_out') : __('orders.post_stock_direction_in'))
                                <p class="text-secondary-light text-sm">{{ __('orders.post_stock_hint', ['direction' => $direction]) }}</p>
                                <form method="POST" action="{{ route('orders.post-stock', $order) }}" class="d-flex flex-wrap gap-2 align-items-end">
                                    @csrf
                                    <div class="flex-grow-1">
                                        <x-input-label for="post_stock_warehouse_id" :value="__('warehouse.title_warehouses')" />
                                        <select id="post_stock_warehouse_id" name="warehouse_id" class="form-select mt-1" required>
                                            @foreach ($warehouses as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <x-primary-button>
                                        <i class="ri-archive-2-line"></i> {{ __('orders.action_post_stock') }}
                                    </x-primary-button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
            @endcan
        </div>

        <div class="col-lg-5">
            <div class="card radius-12 mb-24">
                <div class="card-header bg-base fw-semibold">{{ __('orders.timeline_title') }}</div>
                <div class="card-body">
                    @if ($stages->isEmpty())
                        <p class="text-muted mb-0">{{ __('orders.no_stage_configured') }}</p>
                    @else
                        <x-order-timeline :order="$order" :stages="$stages" />
                    @endif
                </div>
            </div>

            @can('orders.edit')
                <div class="card radius-12 mb-24">
                    <div class="card-header bg-base fw-semibold">{{ __('warehouse.title_shipment') }}</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('orders.shipment.save', $order) }}">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <x-input-label for="carrier_name" :value="__('warehouse.field_carrier_name')" />
                                    <x-text-input id="carrier_name" name="carrier_name" class="mt-1 w-100" :value="$order->shipment?->carrier_name" />
                                </div>
                                <div class="col-md-6">
                                    <x-input-label for="tracking_number" :value="__('warehouse.field_tracking_number')" />
                                    <x-text-input id="tracking_number" name="tracking_number" class="mt-1 w-100" :value="$order->shipment?->tracking_number" />
                                </div>
                                <div class="col-md-6">
                                    <x-input-label for="transport_method_lookup_value_id" :value="__('warehouse.field_transport_method')" />
                                    <select id="transport_method_lookup_value_id" name="transport_method_lookup_value_id" class="form-select mt-1">
                                        <option value="">—</option>
                                        @foreach ($transportMethods as $method)
                                            <option value="{{ $method->id }}" @selected($order->shipment?->transport_method_lookup_value_id === $method->id)>
                                                {{ $method->label[app()->getLocale()] ?? $method->code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <x-input-label for="vehicle_plate" :value="__('warehouse.field_vehicle_plate')" />
                                    <x-text-input id="vehicle_plate" name="vehicle_plate" class="mt-1 w-100" :value="$order->shipment?->vehicle_plate" />
                                </div>
                                <div class="col-md-6">
                                    <x-input-label for="driver_name" :value="__('warehouse.field_driver_name')" />
                                    <x-text-input id="driver_name" name="driver_name" class="mt-1 w-100" :value="$order->shipment?->driver_name" />
                                </div>
                                <div class="col-md-6">
                                    <x-input-label for="cost" :value="__('warehouse.field_cost')" />
                                    <x-text-input id="cost" name="cost" type="number" step="0.01" class="mt-1 w-100" :value="$order->shipment?->cost" />
                                </div>
                                <div class="col-md-4">
                                    <x-input-label for="departure_date" :value="__('warehouse.field_departure_date')" />
                                    <x-text-input id="departure_date" name="departure_date" type="date" class="mt-1 w-100" :value="$order->shipment?->departure_date?->format('Y-m-d')" />
                                </div>
                                <div class="col-md-4">
                                    <x-input-label for="expected_arrival_date" :value="__('warehouse.field_expected_arrival_date')" />
                                    <x-text-input id="expected_arrival_date" name="expected_arrival_date" type="date" class="mt-1 w-100" :value="$order->shipment?->expected_arrival_date?->format('Y-m-d')" />
                                </div>
                                <div class="col-md-4">
                                    <x-input-label for="actual_arrival_date" :value="__('warehouse.field_actual_arrival_date')" />
                                    <x-text-input id="actual_arrival_date" name="actual_arrival_date" type="date" class="mt-1 w-100" :value="$order->shipment?->actual_arrival_date?->format('Y-m-d')" />
                                </div>
                                <div class="col-12">
                                    <x-input-label for="note" :value="__('warehouse.field_note')" />
                                    <textarea id="note" name="note" rows="2" class="form-control mt-1">{{ $order->shipment?->note }}</textarea>
                                </div>
                            </div>
                            <div class="mt-16">
                                <x-primary-button>{{ __('app.save') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            @endcan

            @if (! $order->is_closed && (auth()->user()->can('orders.advance') || auth()->user()->can('orders.advance_any')))
                <div class="card radius-12">
                    <div class="card-header bg-base fw-semibold">{{ __('orders.title_advance_stage') }}</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('orders.advance', $order) }}" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <x-input-label for="lookup_value_id" :value="__('orders.field_target_stage')" />
                                @can('orders.advance_any')
                                    <select id="lookup_value_id" name="lookup_value_id" class="form-select mt-1" required>
                                        @foreach ($stages as $stage)
                                            @continue($stage->sort_order <= ($order->currentStage?->sort_order ?? -1))
                                            <option value="{{ $stage->id }}" @selected($nextStage && $stage->id === $nextStage->id)>
                                                {{ $stage->label[app()->getLocale()] ?? $stage->code }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    @if ($nextStage)
                                        <input type="hidden" name="lookup_value_id" value="{{ $nextStage->id }}">
                                        <p class="form-control-plaintext mt-1 fw-medium">{{ $nextStage->label[app()->getLocale()] ?? $nextStage->code }}</p>
                                    @endif
                                @endcan
                                <x-input-error :messages="$errors->get('lookup_value_id')" class="mt-1" />
                            </div>

                            <div class="mb-3">
                                <x-input-label for="responsible_user_id" :value="__('orders.field_responsible_user')" />
                                <select id="responsible_user_id" name="responsible_user_id" class="form-select mt-1">
                                    <option value="">—</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" @selected(auth()->id() === $user->id)>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('responsible_user_id')" class="mt-1" />
                            </div>

                            <div class="mb-3">
                                <x-input-label for="cost" :value="__('orders.field_cost')" />
                                <x-text-input id="cost" name="cost" type="number" step="0.01" class="mt-1 w-100" />
                                <x-input-error :messages="$errors->get('cost')" class="mt-1" />
                            </div>

                            <div class="mb-3">
                                <x-input-label for="description" :value="__('orders.field_description')" />
                                <textarea id="description" name="description" rows="2" class="form-control mt-1"></textarea>
                                <x-input-error :messages="$errors->get('description')" class="mt-1" />
                            </div>

                            <div class="mb-3">
                                <x-input-label for="comment" :value="__('orders.field_comment')" />
                                <textarea id="comment" name="comment" rows="2" class="form-control mt-1"></textarea>
                                <x-input-error :messages="$errors->get('comment')" class="mt-1" />
                            </div>

                            <div class="mb-3">
                                <x-input-label for="files" :value="__('orders.field_files')" />
                                <input id="files" name="files[]" type="file" multiple class="form-control mt-1">
                                <x-input-error :messages="$errors->get('files')" class="mt-1" />
                            </div>

                            <x-primary-button>
                                <i class="ri-arrow-right-circle-line"></i> {{ __('orders.action_advance') }}
                            </x-primary-button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
