<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ $asset->title }} <code class="text-secondary-light">{{ $asset->asset_code }}</code></h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.assets.label', $asset) }}" target="_blank" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-printer-line"></i> {{ __('assets.action_print_label') }}
                </a>
                @can('assets.edit')
                    <a href="{{ route('admin.assets.edit', $asset) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.edit') }}</a>
                @endcan
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('assets.flash_'.session('status')) }}</div>
    @endif

    @php
        $statusBadge = fn (string $status) => match ($status) {
            'in_use' => 'text-success-600 bg-success-100',
            'in_storage' => 'text-info-600 bg-info-100',
            'under_repair' => 'text-warning-600 bg-warning-100',
            'lost' => 'text-danger-600 bg-danger-100',
            default => 'text-neutral-600 bg-neutral-200',
        };
    @endphp

    <div class="row g-24 mb-24">
        <div class="col-lg-4">
            <div class="card radius-12 h-100">
                <div class="card-body text-center">
                    <div class="d-flex align-items-center justify-content-center bg-neutral-100 radius-8 mx-auto mb-16" style="width: 96px; height: 96px;">
                        @if ($asset->image_url)
                            <img src="{{ $asset->image_url }}" alt="" class="w-100 h-100 object-fit-cover radius-8">
                        @else
                            <i class="ri-archive-2-line text-3xl text-neutral-400"></i>
                        @endif
                    </div>
                    <div class="mb-8">
                        <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $statusBadge($asset->status) }}">
                            {{ __('assets.status_'.$asset->status) }}
                        </span>
                    </div>
                    <div class="text-secondary-light text-sm mb-16">{{ $asset->category?->name ?? '—' }}</div>
                    <div class="d-flex justify-content-center">
                        {!! \App\Support\Barcode::svg($asset->asset_code, 1.5, 40) !!}
                    </div>
                </div>
                <div class="card-footer bg-body">
                    <dl class="row mb-0 text-sm">
                        <dt class="col-6">{{ __('assets.field_serial_number') }}</dt>
                        <dd class="col-6">{{ $asset->serial_number ?? '—' }}</dd>
                        <dt class="col-6">{{ __('assets.field_purchase_date') }}</dt>
                        <dd class="col-6">{{ $asset->purchase_date?->toDateString() ?? '—' }}</dd>
                        <dt class="col-6">{{ __('assets.field_purchase_price') }}</dt>
                        <dd class="col-6">{{ $asset->purchase_price ? number_format((float) $asset->purchase_price, 2) : '—' }}</dd>
                    </dl>
                    @if ($asset->description)
                        <hr>
                        <p class="text-sm mb-0">{{ $asset->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card radius-12">
                <div class="card-body">
                    @if ($asset->currentHolder)
                        <div class="d-flex align-items-center gap-3 mb-16">
                            <img src="{{ $asset->currentHolder->avatar_url }}" alt="" class="w-48-px h-48-px rounded-circle object-fit-cover">
                            <div>
                                <div class="fw-semibold">{{ $asset->currentHolder->name }}</div>
                                <div class="text-secondary-light text-sm">{{ __('assets.field_assigned_at') }}: {{ $asset->assigned_at?->format('Y-m-d H:i') }}</div>
                            </div>
                        </div>

                        @can('assets.assign')
                            <form method="POST" action="{{ route('admin.assets.return', $asset) }}" class="row g-3">
                                @csrf
                                @method('put')
                                <div class="col-md-6">
                                    <x-input-label for="return_status" :value="__('assets.field_status')" />
                                    <select id="return_status" name="status" class="form-select radius-8 mt-1" required>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}" @selected($status === 'in_storage')>{{ __('assets.status_'.$status) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <x-input-label for="return_note" :value="__('assets.field_note')" />
                                    <x-text-input id="return_note" name="note" class="mt-1 w-100" />
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-danger-600 radius-8 px-16 py-8 text-sm">{{ __('assets.action_return') }}</button>
                                </div>
                            </form>
                        @endcan
                    @else
                        <p class="text-secondary-light mb-16">{{ __('assets.unassigned') }}</p>

                        @can('assets.assign')
                            <form method="POST" action="{{ route('admin.assets.assign', $asset) }}" class="row g-3">
                                @csrf
                                <div class="col-md-5">
                                    <x-input-label for="user_id" :value="__('assets.field_employee')" />
                                    <select id="user_id" name="user_id" class="form-select radius-8 mt-1" required>
                                        <option value="">—</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <x-input-label for="location" :value="__('assets.field_location')" />
                                    <select id="location" name="location" class="form-select radius-8 mt-1" required>
                                        <option value="on_site">{{ __('assets.location_on_site') }}</option>
                                        <option value="off_site">{{ __('assets.location_off_site') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <x-input-label for="assign_note" :value="__('assets.field_note')" />
                                    <x-text-input id="assign_note" name="note" class="mt-1 w-100" />
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('assets.action_assign') }}</button>
                                </div>
                            </form>
                        @endcan
                    @endif
                </div>
            </div>

            <div class="card radius-12 mt-24">
                <div class="card-body">
                    <h6 class="mb-16">{{ __('assets.history_title') }}</h6>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('assets.field_employee') }}</th>
                                    <th>{{ __('assets.field_location') }}</th>
                                    <th>{{ __('assets.field_assigned_at') }}</th>
                                    <th>{{ __('assets.field_returned_at') }}</th>
                                    <th>{{ __('assets.field_note') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($asset->assignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->user->name }}</td>
                                        <td>{{ __('assets.location_'.$assignment->location) }}</td>
                                        <td>{{ $assignment->assigned_at->format('Y-m-d H:i') }}</td>
                                        <td>{{ $assignment->returned_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                        <td>{{ $assignment->note ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">{{ __('assets.history_empty') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
