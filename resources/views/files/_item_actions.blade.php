@php
    $isFolder = $item instanceof \App\Models\FileManager\Folder;
    $favKey = ($isFolder ? 'folder' : 'file').':'.$item->id;
    $isFavorited = in_array($favKey, $favoriteKeys ?? [], true);
@endphp

<ul class="dropdown-menu p-8 border bg-base shadow">
    @if ($isFolder)
        <li><a class="dropdown-item px-12 py-6 rounded text-secondary-light" href="{{ route('files.index', ['folder' => $item->id]) }}">{{ __('files.action_open') }}</a></li>
    @else
        <li><a class="dropdown-item px-12 py-6 rounded text-secondary-light" href="{{ route('files.entries.show', $item) }}">{{ __('files.action_preview') }}</a></li>
        <li><a class="dropdown-item px-12 py-6 rounded text-secondary-light" href="{{ route('files.entries.download', $item) }}">{{ __('files.action_download') }}</a></li>
    @endif

    <li>
        <form action="{{ route('files.favorites.toggle') }}" method="POST">
            @csrf
            <input type="hidden" name="favoritable_type" value="{{ $isFolder ? 'folder' : 'file' }}">
            <input type="hidden" name="favoritable_id" value="{{ $item->id }}">
            <button type="submit" class="dropdown-item px-12 py-6 rounded text-secondary-light w-100 text-start border-0 bg-transparent">
                <i class="{{ $isFavorited ? 'ri-star-fill text-warning-main' : 'ri-star-line' }}"></i>
                {{ $isFavorited ? __('files.action_unpin') : __('files.action_pin') }}
            </button>
        </form>
    </li>

    @can('update', $item)
        <li>
            <button type="button" class="dropdown-item px-12 py-6 rounded text-secondary-light w-100 text-start border-0 bg-transparent"
                data-bs-toggle="modal" data-bs-target="#rename-modal"
                data-action="{{ $isFolder ? route('files.folders.update', $item) : route('files.entries.update', $item) }}"
                data-name="{{ $isFolder ? $item->name : ($item->title ?: $item->original_name) }}"
                data-label="{{ $isFolder ? __('files.field_folder_name') : __('files.field_title') }}">
                {{ __('files.action_rename') }}
            </button>
        </li>
        <li>
            <button type="button" class="dropdown-item px-12 py-6 rounded text-secondary-light w-100 text-start border-0 bg-transparent"
                data-bs-toggle="modal" data-bs-target="#move-modal"
                data-action="{{ $isFolder ? route('files.folders.move', $item) : route('files.entries.move', $item) }}">
                {{ __('files.action_move') }}
            </button>
        </li>
        <li>
            <a class="dropdown-item px-12 py-6 rounded text-secondary-light" href="#" data-bs-toggle="modal"
                data-bs-target="#share-modal-{{ $isFolder ? 'folder' : 'file' }}-{{ $item->id }}">
                {{ __('files.action_share') }}
            </a>
        </li>
    @endcan

    @can('markConfidential', $item)
        <li>
            <form action="{{ $isFolder ? route('files.folders.confidential.toggle', $item) : route('files.entries.confidential.toggle', $item) }}"
                method="POST"
                onsubmit="return confirm('{{ $item->is_confidential ? __('files.confirm_unmark_confidential') : __('files.confirm_mark_confidential') }}');">
                @csrf
                <button type="submit" class="dropdown-item px-12 py-6 rounded {{ $item->is_confidential ? 'text-warning-main' : 'text-danger-600' }} w-100 text-start border-0 bg-transparent">
                    <i class="{{ $item->is_confidential ? 'ri-lock-unlock-line' : 'ri-lock-2-line' }}"></i>
                    {{ $item->is_confidential ? __('files.action_unmark_confidential') : __('files.action_mark_confidential') }}
                </button>
            </form>
        </li>
    @endcan

    @if ($isFolder)
        @can('update', $item)
            <li>
                <form action="{{ route('files.folders.destroy', $item) }}" method="POST" onsubmit="return confirm('{{ __('files.confirm_delete_folder') }}');">
                    @csrf
                    @method('delete')
                    <button type="submit" class="dropdown-item px-12 py-6 rounded text-danger-600 w-100 text-start border-0 bg-transparent">{{ __('files.action_delete') }}</button>
                </form>
            </li>
        @endcan
    @else
        @can('delete', $item)
            <li>
                <form action="{{ route('files.entries.destroy', $item) }}" method="POST" onsubmit="return confirm('{{ __('files.confirm_delete_file') }}');">
                    @csrf
                    @method('delete')
                    <button type="submit" class="dropdown-item px-12 py-6 rounded text-danger-600 w-100 text-start border-0 bg-transparent">{{ __('files.action_delete') }}</button>
                </form>
            </li>
        @endcan
    @endif
</ul>
