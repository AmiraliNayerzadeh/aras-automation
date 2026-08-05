<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ $lookupType->name }}</h2>
    </x-slot>

    <div class="card radius-12 mb-24">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.field_code') }}</th>
                        <th>EN</th>
                        <th>HY</th>
                        <th>FA</th>
                        <th>{{ __('app.field_status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lookupType->values as $value)
                        <tr>
                            <td><code>{{ $value->code }}</code></td>
                            <td>{{ $value->label['en'] ?? '—' }}</td>
                            <td>{{ $value->label['hy'] ?? '—' }}</td>
                            <td>{{ $value->label['fa'] ?? '—' }}</td>
                            <td>
                                <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $value->is_active ? 'text-success-600 bg-success-100' : 'text-neutral-600 bg-neutral-200' }}">
                                    {{ $value->is_active ? __('app.field_active') : __('app.field_inactive') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card radius-12">
        <div class="card-header bg-body">{{ __('app.create_new') }}</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.lookup-types.values.store', $lookupType) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-3">
                        <x-input-label for="code" :value="__('app.field_code')" />
                        <x-text-input id="code" name="code" class="mt-1 w-100" required />
                    </div>
                    <div class="col-md-3">
                        <x-input-label for="label_en" value="EN" />
                        <x-text-input id="label_en" name="label_en" class="mt-1 w-100" required />
                    </div>
                    <div class="col-md-2">
                        <x-input-label for="label_hy" value="HY" />
                        <x-text-input id="label_hy" name="label_hy" class="mt-1 w-100" />
                    </div>
                    <div class="col-md-2">
                        <x-input-label for="label_fa" value="FA" />
                        <x-text-input id="label_fa" name="label_fa" class="mt-1 w-100" />
                    </div>
                    <div class="col-md-2">
                        <x-input-label for="color" value="Color" />
                        <x-text-input id="color" name="color" class="mt-1 w-100" placeholder="success" />
                    </div>
                </div>
                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                <div class="mt-3">
                    <x-primary-button>{{ __('app.save') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('admin.lookup-types.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.back') }}</a>
    </div>
</x-app-layout>
