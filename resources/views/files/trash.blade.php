<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('files.title_trash') }}</h2>
            <a href="{{ route('files.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                <i class="ri-arrow-go-back-line"></i> {{ __('app.back') }}
            </a>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('files.flash_'.str_replace('-', '_', session('status'))) }}</div>
    @endif

    @if ($folders->isEmpty() && $files->isEmpty())
        <div class="card radius-12">
            <div class="card-body text-center text-muted py-4">{{ __('files.trash_empty') }}</div>
        </div>
    @else
        <div class="card radius-12">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('files.field_title') }}</th>
                            <th>{{ __('files.field_owner') }}</th>
                            <th class="text-end">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($folders as $folder)
                            <tr>
                                <td><i class="ri-folder-3-fill text-warning-main"></i> {{ $folder->name }}</td>
                                <td>{{ $folder->owner?->name }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('files.folders.restore', $folder->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary-600 btn-sm radius-8 px-12 py-6 text-sm">{{ __('files.action_restore') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        @foreach ($files as $file)
                            <tr>
                                <td><i class="ri-file-3-line"></i> {{ $file->title ?: $file->original_name }}</td>
                                <td>{{ $file->owner?->name }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('files.entries.restore', $file->id) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary-600 btn-sm radius-8 px-12 py-6 text-sm">{{ __('files.action_restore') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
