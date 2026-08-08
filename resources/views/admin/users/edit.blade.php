<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 fw-semibold mb-0">{{ __('app.edit') }} — {{ $user->name }}</h2>
    </x-slot>

    <div class="card radius-12">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data">
                @csrf
                @method('put')
                @include('admin.users._form')
            </form>
        </div>
    </div>

    <div class="card radius-12 mt-24">
        <div class="card-header bg-base fw-semibold">{{ __('app.documents') }}</div>
        <div class="card-body">
            <div class="table-responsive mb-24">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('app.field_category') }}</th>
                            <th>{{ __('app.field_title') }}</th>
                            <th>{{ __('app.field_file') }}</th>
                            <th class="text-end">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($user->documents as $document)
                            <tr>
                                <td>{{ $document->category?->label[app()->getLocale()] ?? '—' }}</td>
                                <td>{{ $document->title ?? '—' }}</td>
                                <td>
                                    <a href="{{ asset('storage/'.$document->file_path) }}" target="_blank">
                                        <i class="ri-attachment-2"></i> {{ $document->original_name }}
                                    </a>
                                </td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.users.documents.destroy', [$user, $document]) }}" onsubmit="return confirm('{{ __('app.confirm_delete') }}');">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-outline-danger-600 radius-8 px-16 py-8 text-sm">{{ __('app.delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('admin.users.documents.store', $user) }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <x-input-label for="category_lookup_value_id" :value="__('app.field_category')" />
                        <select id="category_lookup_value_id" name="category_lookup_value_id" class="form-select radius-8 mt-1">
                            <option value="">—</option>
                            @foreach ($documentCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->label[app()->getLocale()] ?? $category->code }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <x-input-label for="title" :value="__('app.field_title')" />
                        <x-text-input id="title" name="title" class="mt-1 w-100" />
                    </div>
                    <div class="col-md-4">
                        <x-input-label for="file" :value="__('app.field_file')" />
                        <input id="file" name="file" type="file" class="form-control radius-8 mt-1" required>
                    </div>
                </div>
                <x-input-error :messages="$errors->get('file')" class="mt-2" />
                <div class="mt-3">
                    <x-primary-button>{{ __('app.create_new') }}</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
