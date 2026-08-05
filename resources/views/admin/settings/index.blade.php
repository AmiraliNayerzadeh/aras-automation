<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.nav_settings') }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.field_group') }}</th>
                        <th>{{ __('app.field_key') }}</th>
                        <th>{{ __('app.field_value') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($settings as $setting)
                        <tr>
                            <td>{{ $setting->group }}</td>
                            <td><code>{{ $setting->key }}</code></td>
                            <td>
                                <form action="{{ route('admin.settings.update', $setting) }}" method="POST" class="d-flex gap-2">
                                    @csrf
                                    @method('put')
                                    <input type="text" name="value" value="{{ $setting->value }}" class="form-control form-control-sm">
                                    <button type="submit" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm text-nowrap">{{ __('app.save') }}</button>
                                </form>
                            </td>
                            <td class="text-end">
                                <span class="badge text-sm fw-semibold px-16 py-6 radius-4 {{ $setting->is_public ? 'text-success-600 bg-success-100' : 'text-neutral-600 bg-neutral-200' }}">
                                    {{ $setting->is_public ? __('app.field_active') : __('app.field_inactive') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.lookup-types.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.lookup_values') }}</a>
    </div>
</x-app-layout>
