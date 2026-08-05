<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.lookup_values') }}</h2>
    </x-slot>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                    <tr>
                        <th>{{ __('app.field_name') }}</th>
                        <th>{{ __('app.field_code') }}</th>
                        <th>{{ __('app.lookup_values') }}</th>
                        <th class="text-end">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lookupTypes as $lookupType)
                        <tr>
                            <td>{{ $lookupType->name }}</td>
                            <td><code>{{ $lookupType->code }}</code></td>
                            <td>{{ $lookupType->values_count }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.lookup-types.edit', $lookupType) }}" class="btn btn-outline-secondary btn-sm">{{ __('app.view') }}</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
