<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('files.title_index') }}</h2>
            <div class="d-flex gap-2">
                <button type="button" data-tree-toggle aria-controls="files-tree-panel" aria-pressed="{{ $treeHidden ? 'false' : 'true' }}"
                    class="btn btn-sm {{ $treeHidden ? 'btn-outline-secondary-600' : 'btn-primary-600' }} radius-8 px-12"
                    title="{{ __('files.tree_toggle') }}" aria-label="{{ __('files.tree_toggle') }}">
                    <i class="ri-node-tree"></i>
                </button>
                <div class="btn-group" role="group">
                    <a href="{{ route('files.index', array_merge(request()->except('view'), ['view' => 'grid'])) }}"
                        class="btn btn-sm {{ $view === 'grid' ? 'btn-primary-600' : 'btn-outline-secondary-600' }} radius-8 px-12"
                        title="{{ __('files.view_grid') }}">
                        <i class="ri-grid-fill"></i>
                    </a>
                    <a href="{{ route('files.index', array_merge(request()->except('view'), ['view' => 'list'])) }}"
                        class="btn btn-sm {{ $view === 'list' ? 'btn-primary-600' : 'btn-outline-secondary-600' }} radius-8 px-12"
                        title="{{ __('files.view_list') }}">
                        <i class="ri-list-check-2"></i>
                    </a>
                </div>
                <a href="{{ route('files.trash') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-delete-bin-6-line"></i> {{ __('files.action_view_trash') }}
                </a>
                <button type="button" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm" data-bs-toggle="modal" data-bs-target="#folder-create-modal">
                    <i class="ri-folder-add-line"></i> {{ __('files.action_new_folder') }}
                </button>
                <button type="button" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm" data-bs-toggle="modal" data-bs-target="#file-upload-modal">
                    <i class="ri-upload-2-line"></i> {{ __('files.action_upload') }}
                </button>
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('files.flash_'.str_replace('-', '_', session('status'))) }}</div>
    @endif

    @include('files._quick_access', ['quickAccess' => $quickAccess])

    <div class="files-layout {{ $treeHidden ? 'is-tree-hidden' : '' }}" data-files-layout>
    <aside class="files-tree card radius-12" id="files-tree-panel">
        <div class="files-tree__head">
            <span class="fw-semibold text-sm d-flex align-items-center gap-2">
                <i class="ri-node-tree text-primary-600"></i> {{ __('files.tree_title') }}
            </span>
            <button type="button" class="files-tree__close" data-tree-close title="{{ __('files.tree_hide') }}" aria-label="{{ __('files.tree_hide') }}">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <nav class="files-tree__body" aria-label="{{ __('files.tree_title') }}">
            <ul class="ft" data-tree-root></ul>
        </nav>
    </aside>

    <div class="files-main">

    @unless ($currentFolder)
        <ul class="nav nav-pills mb-24">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'mine' ? 'active' : '' }}" href="{{ route('files.index', ['tab' => 'mine', 'view' => $view]) }}">{{ __('files.tab_mine') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'shared' ? 'active' : '' }}" href="{{ route('files.index', ['tab' => 'shared', 'view' => $view]) }}">{{ __('files.tab_shared') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'all' ? 'active' : '' }}" href="{{ route('files.index', ['tab' => 'all', 'view' => $view]) }}">{{ __('files.tab_all') }}</a>
            </li>
        </ul>
    @else
        <div class="d-flex align-items-center gap-3 mb-24 flex-wrap">
            <a href="{{ $currentFolder->parent_id ? route('files.index', ['folder' => $currentFolder->parent_id, 'tab' => $tab, 'view' => $view]) : route('files.index', ['tab' => $tab, 'view' => $view]) }}"
                class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm d-flex align-items-center gap-1 flex-shrink-0">
                <i class="ri-arrow-left-line"></i> {{ __('app.back') }}
            </a>
            <nav class="flex-grow-1 overflow-auto">
                <ol class="breadcrumb mb-0 bg-neutral-50 radius-8 px-16 py-10 border">
                    <li class="breadcrumb-item" data-drop-target data-drop-id="" data-drop-name="{{ __('files.move_confirm_root') }}">
                        <a href="{{ route('files.index', ['tab' => $tab, 'view' => $view]) }}" class="d-flex align-items-center gap-1">
                            <i class="ri-home-4-line"></i> {{ __('files.breadcrumb_home') }}
                        </a>
                    </li>
                    @foreach ($breadcrumb as $crumb)
                        <li class="breadcrumb-item {{ $loop->last ? 'active fw-semibold' : '' }}"
                            @if (! $loop->last && auth()->user()->can('update', $crumb))
                                data-drop-target data-drop-id="{{ $crumb->id }}" data-drop-name="{{ $crumb->name }}"
                            @endif>
                            @if ($loop->last || ! auth()->user()->can('view', $crumb))
                                {{ $crumb->name }}
                            @else
                                <a href="{{ route('files.index', ['folder' => $crumb->id, 'tab' => $tab, 'view' => $view]) }}">{{ $crumb->name }}</a>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        </div>
    @endunless

    <form method="GET" action="{{ route('files.index') }}" class="d-flex align-items-center gap-2 mb-16 flex-wrap">
        @if ($currentFolder)
            <input type="hidden" name="folder" value="{{ $currentFolder->id }}">
        @endif
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="hidden" name="view" value="{{ $view }}">
        <i class="ri-sort-desc text-secondary-light"></i>
        <select name="sort" class="form-select form-select-sm w-auto" onchange="this.form.submit()" aria-label="{{ __('files.sort_label') }}">
            @foreach (\App\Http\Controllers\FileManager\FileManagerController::SORTS as $sortOption)
                <option value="{{ $sortOption }}" @selected($sort === $sortOption)>{{ __('files.sort_'.$sortOption) }}</option>
            @endforeach
        </select>
        @if ($fileLabels->isNotEmpty())
            <i class="ri-price-tag-3-line text-secondary-light ms-8"></i>
            <select name="label" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <option value="">{{ __('files.filter_all_labels') }}</option>
                @foreach ($fileLabels as $fileLabel)
                    <option value="{{ $fileLabel->id }}" @selected($labelFilterId === $fileLabel->id)>
                        {{ $fileLabel->label[app()->getLocale()] ?? $fileLabel->code }}
                    </option>
                @endforeach
            </select>
            @if ($labelFilterId)
                <a href="{{ route('files.index', array_filter(['folder' => $currentFolder?->id, 'tab' => $tab, 'view' => $view, 'sort' => $sort])) }}" class="text-sm text-secondary-light">
                    <i class="ri-close-line"></i> {{ __('files.filter_clear') }}
                </a>
            @endif
        @endif
    </form>

    @if ($folders->isEmpty() && $files->isEmpty())
        <div class="card radius-12">
            <div class="card-body text-center text-muted py-4">{{ __('files.empty_folder') }}</div>
        </div>
    @elseif ($view === 'list')
        <div class="card radius-12">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('app.field_name') }}</th>
                            <th>{{ __('files.field_owner') }}</th>
                            <th>{{ __('files.field_access') }}</th>
                            <th>{{ __('files.field_size') }}</th>
                            <th>{{ __('files.field_updated_at') }}</th>
                            <th class="text-end">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($folders as $folder)
                            <tr data-context-menu
                                @can('update', $folder)
                                    data-drag-item data-drag-id="{{ $folder->id }}" data-drag-type="folder" data-drag-name="{{ $folder->name }}"
                                    data-drop-target data-drop-id="{{ $folder->id }}" data-drop-name="{{ $folder->name }}"
                                @endcan>
                                <td>
                                    <a href="{{ route('files.index', ['folder' => $folder->id, 'view' => $view]) }}" class="text-primary-light d-flex align-items-center gap-2">
                                        <i class="ri-folder-3-fill text-warning-main"></i> {{ $folder->name }}
                                        @if ($folder->is_confidential)
                                            <i class="ri-lock-2-fill text-danger-600 text-sm" title="{{ __('files.badge_confidential') }}"></i>
                                        @endif
                                    </a>
                                    @include('files._labels', ['item' => $folder, 'class' => 'mt-4'])
                                </td>
                                <td>{{ $folder->owner?->name }}</td>
                                <td>@include('files._access_avatars', ['item' => $folder])</td>
                                <td>—</td>
                                <td>{{ $folder->updated_at->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy": "fixed"}' aria-expanded="false" class="w-32-px h-32-px radius-8 border d-flex justify-content-center align-items-center bg-base ms-auto">
                                            <i class="ri-more-2-fill"></i>
                                        </button>
                                        @include('files._item_actions', ['item' => $folder])
                                    </div>
                                </td>
                            </tr>
                            @can('update', $folder)
                                @include('files._share_modal', ['shareable' => $folder, 'modalId' => 'share-modal-folder-'.$folder->id, 'storeRoute' => route('files.folders.shares.store', $folder), 'destroyRouteBase' => 'files.folders.shares.destroy', 'destroyParam' => $folder])
                            @endcan
                        @endforeach

                        @foreach ($files as $file)
                            <tr data-context-menu
                                @can('update', $file)
                                    data-drag-item data-drag-id="{{ $file->id }}" data-drag-type="file" data-drag-name="{{ $file->title ?: $file->original_name }}"
                                @endcan>
                                <td>
                                    <a href="{{ route('files.entries.show', $file) }}" class="text-primary-light d-flex align-items-center gap-2">
                                        <i class="{{ $file->iconClass() }} {{ $file->iconColorClass() }}"></i>
                                        {{ $file->title ?: $file->original_name }}
                                        @if ($file->is_confidential)
                                            <i class="ri-lock-2-fill text-danger-600 text-sm" title="{{ __('files.badge_confidential') }}"></i>
                                        @endif
                                    </a>
                                    @include('files._labels', ['item' => $file, 'class' => 'mt-4'])
                                </td>
                                <td>{{ $file->owner?->name }}</td>
                                <td>@include('files._access_avatars', ['item' => $file])</td>
                                <td>{{ $file->human_size }}</td>
                                <td>{{ $file->updated_at->format('Y-m-d H:i') }}</td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy": "fixed"}' aria-expanded="false" class="w-32-px h-32-px radius-8 border d-flex justify-content-center align-items-center bg-base ms-auto">
                                            <i class="ri-more-2-fill"></i>
                                        </button>
                                        @include('files._item_actions', ['item' => $file])
                                    </div>
                                </td>
                            </tr>
                            @can('update', $file)
                                @include('files._share_modal', ['shareable' => $file, 'modalId' => 'share-modal-file-'.$file->id, 'storeRoute' => route('files.entries.shares.store', $file), 'destroyRouteBase' => 'files.entries.shares.destroy', 'destroyParam' => $file])
                            @endcan
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="files-grid">
            @foreach ($folders as $folder)
                <div>
                    <div class="border radius-12 p-16 h-100 position-relative {{ $folder->color ? '' : 'bg-base' }}" data-context-menu
                        @if ($folder->color) style="background-color: {{ $folder->color }};" @endif
                        @can('update', $folder)
                            data-drag-item data-drag-id="{{ $folder->id }}" data-drag-type="folder" data-drag-name="{{ $folder->name }}"
                            data-drop-target data-drop-id="{{ $folder->id }}" data-drop-name="{{ $folder->name }}"
                        @endcan>
                        <div class="dropdown position-absolute top-0 end-0 me-8 mt-8">
                            <button type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy": "fixed"}' aria-expanded="false" class="w-32-px h-32-px radius-8 border d-flex justify-content-center align-items-center bg-base">
                                <i class="ri-more-2-fill"></i>
                            </button>
                            @include('files._item_actions', ['item' => $folder])
                        </div>
                        <a href="{{ route('files.index', ['folder' => $folder->id, 'view' => $view]) }}" class="d-flex flex-column align-items-center text-center text-decoration-none py-16">
                            <i class="ri-folder-3-fill text-warning-main" style="font-size: 48px;"></i>
                            <span class="text-primary-light fw-medium text-sm mt-8 text-truncate w-100">
                                {{ $folder->name }}
                                @if ($folder->is_confidential)
                                    <i class="ri-lock-2-fill text-danger-600" title="{{ __('files.badge_confidential') }}"></i>
                                @endif
                            </span>
                            <span class="text-secondary-light text-xs">{{ $folder->owner?->name }}</span>
                        </a>
                        @include('files._labels', ['item' => $folder, 'class' => 'justify-content-center'])
                        <div class="d-flex justify-content-center mt-4">
                            @include('files._access_avatars', ['item' => $folder])
                        </div>
                    </div>
                </div>
                @can('update', $folder)
                    @include('files._share_modal', ['shareable' => $folder, 'modalId' => 'share-modal-folder-'.$folder->id, 'storeRoute' => route('files.folders.shares.store', $folder), 'destroyRouteBase' => 'files.folders.shares.destroy', 'destroyParam' => $folder])
                @endcan
            @endforeach

            @foreach ($files as $file)
                <div>
                    <div class="border radius-12 h-100 bg-base position-relative" data-context-menu
                        @can('update', $file)
                            data-drag-item data-drag-id="{{ $file->id }}" data-drag-type="file" data-drag-name="{{ $file->title ?: $file->original_name }}"
                        @endcan>
                        <div class="dropdown position-absolute top-0 end-0 me-8 mt-8 z-1">
                            <button type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy": "fixed"}' aria-expanded="false" class="w-32-px h-32-px radius-8 border d-flex justify-content-center align-items-center bg-base">
                                <i class="ri-more-2-fill"></i>
                            </button>
                            @include('files._item_actions', ['item' => $file])
                        </div>
                        <a href="{{ route('files.entries.show', $file) }}" class="d-block bg-neutral-100 d-flex align-items-center justify-content-center overflow-hidden" style="height: 120px; border-radius: 12px 12px 0 0;">
                            @if ($file->isImage())
                                <img src="{{ asset('storage/'.$file->file_path) }}" alt="" class="w-100 h-100 object-fit-cover">
                            @else
                                <i class="{{ $file->iconClass() }} {{ $file->iconColorClass() }}" style="font-size: 40px;"></i>
                            @endif
                        </a>
                        <div class="p-12">
                            <div class="text-primary-light fw-medium text-sm text-truncate" title="{{ $file->title ?: $file->original_name }}">
                                <a href="{{ route('files.entries.show', $file) }}" class="text-primary-light">{{ $file->title ?: $file->original_name }}</a>
                                @if ($file->is_confidential)
                                    <i class="ri-lock-2-fill text-danger-600" title="{{ __('files.badge_confidential') }}"></i>
                                @endif
                            </div>
                            <div class="text-secondary-light text-xs d-flex justify-content-between mt-4">
                                <span>{{ $file->owner?->name }}</span>
                                <span>{{ $file->human_size }}</span>
                            </div>
                            @include('files._labels', ['item' => $file, 'class' => 'mt-8'])
                            <div class="mt-8">
                                @include('files._access_avatars', ['item' => $file])
                            </div>
                        </div>
                    </div>
                </div>
                @can('update', $file)
                    @include('files._share_modal', ['shareable' => $file, 'modalId' => 'share-modal-file-'.$file->id, 'storeRoute' => route('files.entries.shares.store', $file), 'destroyRouteBase' => 'files.entries.shares.destroy', 'destroyParam' => $file])
                @endcan
            @endforeach
        </div>
    @endif

    </div>{{-- .files-main --}}
    </div>{{-- .files-layout --}}

    {{-- New Folder modal --}}
    <div class="modal fade" id="folder-create-modal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('files.folders.store') }}" class="modal-content">
                @csrf
                <input type="hidden" name="parent_id" value="{{ $currentFolder?->id }}">
                <div class="modal-header">
                    <h6 class="modal-title">{{ __('files.action_new_folder') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <x-input-label for="new_folder_name" :value="__('files.field_folder_name')" />
                    <x-text-input id="new_folder_name" name="name" class="mt-1 w-100" required autofocus />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Upload modal --}}
    <div class="modal fade" id="file-upload-modal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('files.entries.store') }}" enctype="multipart/form-data" class="modal-content" id="upload-form" data-upload-form>
                @csrf
                <input type="hidden" name="folder_id" value="{{ $currentFolder?->id }}">
                <div class="modal-header">
                    <h6 class="modal-title">{{ __('files.action_upload') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <x-input-label for="upload_title" :value="__('files.field_title')" />
                        <x-text-input id="upload_title" name="title" class="mt-1 w-100" />
                    </div>
                    <div>
                        <x-input-label for="upload_file" :value="__('files.field_file')" />
                        <input type="file" id="upload_file" name="file" class="form-control mt-1" required>
                    </div>
                    <div class="mt-3 d-none" data-upload-progress-wrap>
                        <div class="progress radius-8" style="height: 8px;">
                            <div class="progress-bar bg-primary-600" role="progressbar" style="width: 0%" data-upload-progress-bar></div>
                        </div>
                        <div class="text-secondary-light text-xs mt-4" data-upload-progress-text">0%</div>
                    </div>
                    <div class="text-danger-600 text-sm mt-2 d-none" data-upload-error></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm" data-upload-submit>{{ __('files.action_upload') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Drag-and-drop overlay (shown while dragging files anywhere over the page) --}}
    <div class="position-fixed top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center flex-column"
        id="drop-overlay" style="background: rgba(72, 127, 255, 0.12); border: 3px dashed #487FFF; z-index: 1080; pointer-events: none;">
        <i class="ri-upload-cloud-2-line" style="font-size: 64px; color: #487FFF;"></i>
        <div class="text-primary-600 fw-semibold mt-2">{{ __('files.drop_hint') }}</div>
    </div>

    {{-- Background upload progress toast (for drag-and-drop uploads) --}}
    <div class="position-fixed bottom-0 end-0 m-24 d-none" id="upload-toast" style="z-index: 1090; width: 320px;">
        <div class="card radius-12 shadow">
            <div class="card-body p-16">
                <div class="d-flex justify-content-between align-items-center mb-8">
                    <span class="text-sm fw-semibold" id="upload-toast-title">{{ __('files.action_upload') }}</span>
                    <span class="text-secondary-light text-xs" id="upload-toast-count"></span>
                </div>
                <div class="text-secondary-light text-xs mb-4 text-truncate" id="upload-toast-filename"></div>
                <div class="progress radius-8" style="height: 6px;">
                    <div class="progress-bar bg-primary-600" role="progressbar" style="width: 0%" id="upload-toast-bar"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Rename modal (shared, JS-populated) --}}
    <div class="modal fade" id="rename-modal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="" id="rename-form" class="modal-content">
                @csrf
                @method('put')
                <div class="modal-header">
                    <h6 class="modal-title">{{ __('files.action_rename') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <x-input-label for="rename_input" id="rename-label" :value="__('files.field_folder_name')" />
                    <input type="text" id="rename_input" name="name" class="form-control mt-1" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Move modal (shared, JS-populated) --}}
    <div class="modal fade" id="move-modal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="" id="move-form" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title">{{ __('files.move_title') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <x-input-label :value="__('files.field_destination')" />
                    <select name="parent_id" class="form-select mt-1">
                        <option value="">{{ __('files.destination_root') }}</option>
                        @foreach ($moveDestinations as $destination)
                            <option value="{{ $destination->id }}">{{ $destination->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('files.action_move_here') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Labels modal (shared, JS-populated) --}}
    <div class="modal fade" id="labels-modal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="" id="labels-form" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title">{{ __('files.labels_title') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @foreach ($fileLabels as $fileLabel)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="labels[]" value="{{ $fileLabel->id }}" id="label-check-{{ $fileLabel->id }}">
                            <label class="form-check-label" for="label-check-{{ $fileLabel->id }}">
                                <span class="badge text-xs fw-semibold px-8 py-2 radius-4 text-{{ $fileLabel->color }}-600 bg-{{ $fileLabel->color }}-100">
                                    {{ $fileLabel->label[app()->getLocale()] ?? $fileLabel->code }}
                                </span>
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('app.save') }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Drag-and-drop move confirmation modal --}}
    <div class="modal fade" id="drag-move-modal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="" id="drag-move-form" class="modal-content">
                @csrf
                <input type="hidden" name="parent_id" id="drag-move-parent-id" value="">
                <div class="modal-header">
                    <h6 class="modal-title">{{ __('files.move_confirm_title') }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0" id="drag-move-confirm-text"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('files.action_move_here') }}</button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
        <style>
            .files-layout { display: flex; align-items: flex-start; gap: 24px; }
            .files-main { flex: 1 1 auto; min-width: 0; }
            .files-tree {
                flex: 0 0 280px; width: 280px; position: sticky; top: 5.5rem;
                max-height: calc(100vh - 7rem); display: flex; flex-direction: column; overflow: hidden;
            }
            .files-layout.is-tree-hidden .files-tree { display: none; }
            .files-tree__head {
                display: flex; align-items: center; justify-content: space-between; gap: 8px;
                padding: 12px 12px 12px 16px; border-bottom: 1px solid var(--border-color);
                color: var(--text-primary-light);
            }
            [dir="rtl"] .files-tree__head { padding: 12px 16px 12px 12px; }
            .files-tree__close {
                width: 28px; height: 28px; border: 0; border-radius: 8px; background: transparent;
                color: var(--text-secondary-light); display: inline-flex; align-items: center; justify-content: center;
            }
            .files-tree__close:hover { background: var(--neutral-100); color: var(--text-primary-light); }
            .files-tree__body { flex: 1 1 auto; overflow: auto; overscroll-behavior: contain; padding: 8px; }
            .ft, .ft ul { list-style: none; margin: 0; padding: 0; }
            .ft ul { margin-inline-start: 11px; padding-inline-start: 6px; border-inline-start: 1px solid var(--neutral-200); }
            .ft ul[hidden] { display: none; }
            .ft-row {
                display: flex; align-items: center; gap: 2px; min-height: 34px; border-radius: 8px;
                padding-inline-end: 6px; transition: background-color .15s ease;
            }
            .ft-row:hover { background: var(--neutral-100); }
            .ft-row.is-active { background: rgba(72, 127, 255, .12); }
            .ft-toggle, .ft-spacer { flex: 0 0 22px; width: 22px; height: 22px; }
            .ft-toggle {
                border: 0; padding: 0; border-radius: 6px; background: transparent; color: var(--text-secondary-light);
                display: inline-flex; align-items: center; justify-content: center;
            }
            .ft-toggle:hover { background: var(--neutral-200); color: var(--text-primary-light); }
            .ft-toggle i { font-size: 18px; line-height: 1; transition: transform .15s ease; }
            [dir="rtl"] .ft-toggle i { transform: scaleX(-1); }
            .ft-item.is-expanded > .ft-row .ft-toggle i { transform: rotate(90deg); }
            .ft-link {
                display: flex; align-items: center; gap: 8px; flex: 1 1 auto; min-width: 0; padding: 6px 4px;
                border-radius: 6px; color: var(--text-primary-light); font-size: 14px; text-decoration: none;
            }
            .ft-link:hover { color: var(--primary-600); }
            .ft-link:focus-visible { outline: 2px solid var(--primary-600); outline-offset: -2px; }
            .ft-link > i { flex: 0 0 auto; font-size: 18px; line-height: 1; }
            .ft-name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .ft-row.is-path .ft-link { font-weight: 500; }
            .ft-row.is-active .ft-link { color: var(--primary-600); font-weight: 600; }
            .ft-file .ft-link { font-size: 13px; color: var(--text-secondary-light); }
            .ft-file .ft-link:hover { color: var(--primary-600); }
            .ft-lock { flex: 0 0 auto; font-size: 13px; }
            .ft-status { display: flex; align-items: center; gap: 6px; padding: 6px 8px; font-size: 13px; color: var(--text-secondary-light); }
            .ft-status button { border: 0; background: none; padding: 0; color: var(--primary-600); text-decoration: underline; font-size: 13px; }
            .ft-spin { display: inline-block; animation: ft-spin .8s linear infinite; }
            @keyframes ft-spin { to { transform: rotate(360deg); } }
            /* Bootstrap's LTR-only breadcrumb separator, mirrored for RTL. */
            [dir="rtl"] .breadcrumb-item + .breadcrumb-item { padding-left: 0; padding-right: var(--bs-breadcrumb-item-padding-x, .5rem); }
            [dir="rtl"] .breadcrumb-item + .breadcrumb-item::before { float: right; padding-right: 0; padding-left: var(--bs-breadcrumb-item-padding-x, .5rem); }
            .files-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 1rem; }
            @media (max-width: 991.98px) {
                .files-layout { flex-direction: column; align-items: stretch; }
                .files-tree { flex: 0 0 auto; width: 100%; position: static; max-height: 360px; }
            }
            @media (prefers-reduced-motion: reduce) {
                .ft-row, .ft-toggle i { transition: none; }
                .ft-spin { animation-duration: 2s; }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            // --- Folder tree: lazy-loaded, expanded along the current path, hideable. ---
            (function () {
                var layout = document.querySelector('[data-files-layout]');
                var treeRoot = document.querySelector('[data-tree-root]');
                var treeBody = treeRoot.closest('.files-tree__body');
                var toggleBtn = document.querySelector('[data-tree-toggle]');
                var closeBtn = document.querySelector('[data-tree-close]');
                var isRtl = document.documentElement.getAttribute('dir') === 'rtl';

                var levels = @json($treeLevels);
                var path = @json($treePath);
                var currentId = @json($currentFolder?->id);
                var treeUrl = @json(route('files.tree'));
                var homeUrl = @json(route('files.index'));
                var folderUrlTemplate = @json(route('files.index', ['folder' => '__ID__']));
                var t = {
                    home: @json(__('files.breadcrumb_home')),
                    expand: @json(__('files.tree_expand')),
                    collapse: @json(__('files.tree_collapse')),
                    loading: @json(__('files.tree_loading')),
                    error: @json(__('files.tree_load_error')),
                    retry: @json(__('files.tree_retry')),
                    empty: @json(__('files.tree_empty')),
                    confidential: @json(__('files.badge_confidential')),
                };

                function make(tag, className, text) {
                    var node = document.createElement(tag);
                    if (className) {
                        node.className = className;
                    }
                    if (text !== undefined) {
                        node.textContent = text;
                    }
                    return node;
                }

                function icon(className) {
                    var i = make('i', className);
                    i.setAttribute('aria-hidden', 'true');
                    return i;
                }

                function buildLink(href, iconClass, name, confidential) {
                    var link = make('a', 'ft-link');
                    link.href = href;
                    link.title = name;
                    link.tabIndex = -1;
                    link.appendChild(icon(iconClass));
                    var label = make('span', 'ft-name', name);
                    label.dir = 'auto';
                    link.appendChild(label);
                    if (confidential) {
                        var lock = icon('ri-lock-2-fill text-danger-600 ft-lock');
                        lock.title = t.confidential;
                        link.appendChild(lock);
                    }
                    return link;
                }

                // A folder node. `folderId` is null for the "Home" root, which is
                // always expanded and has no toggle.
                function buildFolder(folderId, name, confidential, hasChildren) {
                    var item = make('li', 'ft-item');
                    var row = make('div', 'ft-row');
                    var href = folderId === null ? homeUrl : folderUrlTemplate.replace('__ID__', folderId);
                    var link = buildLink(href, folderId === null ? 'ri-home-4-line text-primary-600' : 'ri-folder-3-fill text-warning-main', name, confidential);

                    item._folderId = folderId;
                    item._loaded = false;
                    item._loading = false;

                    if (hasChildren && folderId !== null) {
                        var toggle = make('button', 'ft-toggle');
                        toggle.type = 'button';
                        toggle.tabIndex = -1;
                        toggle.setAttribute('aria-expanded', 'false');
                        toggle.setAttribute('aria-label', t.expand + ': ' + name);
                        toggle.appendChild(icon('ri-arrow-right-s-line'));
                        toggle.addEventListener('click', function () {
                            setExpanded(item, !item.classList.contains('is-expanded'));
                        });
                        row.appendChild(toggle);
                        item._toggle = toggle;
                    } else {
                        row.appendChild(make('span', 'ft-spacer'));
                    }

                    row.appendChild(link);
                    item.appendChild(row);

                    if (hasChildren || folderId === null) {
                        var group = make('ul');
                        group.hidden = true;
                        item.appendChild(group);
                        item._group = group;
                    }

                    item._row = row;
                    item._link = link;
                    return item;
                }

                function buildFile(file) {
                    var item = make('li', 'ft-item ft-file');
                    var row = make('div', 'ft-row');
                    row.appendChild(make('span', 'ft-spacer'));
                    row.appendChild(buildLink(file.url, file.icon, file.name, file.confidential));
                    item.appendChild(row);
                    return item;
                }

                function setStatus(group, content) {
                    group.textContent = '';
                    var li = make('li', 'ft-status');
                    content.forEach(function (part) {
                        li.appendChild(part);
                    });
                    group.appendChild(li);
                }

                function renderLevel(item, level) {
                    var group = item._group;
                    group.textContent = '';
                    if (!level.folders.length && !level.files.length) {
                        setStatus(group, [make('span', null, t.empty)]);
                    }
                    level.folders.forEach(function (folder) {
                        group.appendChild(buildFolder(folder.id, folder.name, folder.confidential, folder.has_children));
                    });
                    level.files.forEach(function (file) {
                        group.appendChild(buildFile(file));
                    });
                    item._loaded = true;
                }

                function load(item) {
                    if (item._loaded || item._loading) {
                        return;
                    }
                    var key = item._folderId === null ? 'root' : item._folderId;
                    if (levels[key]) {
                        renderLevel(item, levels[key]);
                        return;
                    }

                    item._loading = true;
                    setStatus(item._group, [icon('ri-loader-4-line ft-spin'), make('span', null, t.loading)]);

                    fetch(treeUrl + '?folder=' + encodeURIComponent(item._folderId), {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin',
                    }).then(function (response) {
                        if (!response.ok) {
                            throw new Error(response.status);
                        }
                        return response.json();
                    }).then(function (level) {
                        levels[key] = level;
                        item._loading = false;
                        renderLevel(item, level);
                    }).catch(function () {
                        item._loading = false;
                        var retry = make('button', null, t.retry);
                        retry.type = 'button';
                        retry.addEventListener('click', function () {
                            load(item);
                        });
                        setStatus(item._group, [icon('ri-error-warning-line text-danger-600'), make('span', null, t.error), retry]);
                    });
                }

                function setExpanded(item, expanded) {
                    if (!item._group || item._folderId === null) {
                        return;
                    }
                    item.classList.toggle('is-expanded', expanded);
                    item._group.hidden = !expanded;
                    item._toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                    item._toggle.setAttribute('aria-label', (expanded ? t.collapse : t.expand) + ': ' + item._link.title);
                    var folderIcon = item._link.querySelector('i');
                    folderIcon.className = (expanded ? 'ri-folder-open-fill' : 'ri-folder-3-fill') + ' text-warning-main';
                    if (expanded) {
                        load(item);
                    }
                }

                function findChildFolder(item, folderId) {
                    var children = item._group ? item._group.children : [];
                    for (var i = 0; i < children.length; i++) {
                        if (children[i]._folderId === folderId) {
                            return children[i];
                        }
                    }
                    return null;
                }

                // Build: Home > ... > current folder, expanded along the way.
                var home = buildFolder(null, t.home, false, true);
                home.classList.add('is-expanded');
                home._group.hidden = false;
                treeRoot.appendChild(home);
                load(home);

                var active = home;
                path.forEach(function (folderId) {
                    var next = findChildFolder(active, folderId);
                    if (!next) {
                        return;
                    }
                    active._row.classList.add('is-path');
                    active = next;
                    setExpanded(active, true);
                });

                // A current folder that couldn't be placed in the tree (an
                // ancestor isn't visible to you) is simply left unhighlighted.
                if (currentId === null || active._folderId === currentId) {
                    active._row.classList.add('is-active');
                    active._link.setAttribute('aria-current', 'page');
                }
                active._link.tabIndex = 0;

                function scrollActiveIntoView() {
                    if (layout.classList.contains('is-tree-hidden')) {
                        return;
                    }
                    var rowRect = active._row.getBoundingClientRect();
                    var bodyRect = treeBody.getBoundingClientRect();
                    if (rowRect.top < bodyRect.top || rowRect.bottom > bodyRect.bottom) {
                        treeBody.scrollTop += rowRect.top - bodyRect.top - (treeBody.clientHeight / 2);
                    }
                }
                scrollActiveIntoView();

                // Keyboard: one tab stop for the whole tree, arrows to move
                // around (mirrored for RTL), Enter follows the link.
                function visibleLinks() {
                    return Array.prototype.filter.call(treeRoot.querySelectorAll('.ft-link'), function (link) {
                        return link.offsetParent !== null;
                    });
                }

                function focusLink(link) {
                    if (!link) {
                        return;
                    }
                    treeRoot.querySelectorAll('.ft-link[tabindex="0"]').forEach(function (other) {
                        other.tabIndex = -1;
                    });
                    link.tabIndex = 0;
                    link.focus();
                }

                treeRoot.addEventListener('keydown', function (event) {
                    var link = event.target.closest('.ft-link');
                    if (!link) {
                        return;
                    }
                    var item = link.closest('.ft-item');
                    var links = visibleLinks();
                    var index = links.indexOf(link);
                    var forward = isRtl ? 'ArrowLeft' : 'ArrowRight';
                    var back = isRtl ? 'ArrowRight' : 'ArrowLeft';

                    if (event.key === 'ArrowDown') {
                        focusLink(links[index + 1]);
                    } else if (event.key === 'ArrowUp') {
                        focusLink(links[index - 1]);
                    } else if (event.key === 'Home') {
                        focusLink(links[0]);
                    } else if (event.key === 'End') {
                        focusLink(links[links.length - 1]);
                    } else if (event.key === forward) {
                        if (item._toggle && !item.classList.contains('is-expanded')) {
                            setExpanded(item, true);
                        } else if (item._group && item.classList.contains('is-expanded')) {
                            var firstChild = item._group.querySelector('.ft-link');
                            focusLink(firstChild);
                        }
                    } else if (event.key === back) {
                        if (item._toggle && item.classList.contains('is-expanded')) {
                            setExpanded(item, false);
                        } else {
                            var parentItem = item.parentElement.closest('.ft-item');
                            focusLink(parentItem ? parentItem._link : null);
                        }
                    } else {
                        return;
                    }
                    event.preventDefault();
                });

                treeRoot.addEventListener('focusin', function (event) {
                    if (event.target.classList.contains('ft-link')) {
                        focusLink(event.target);
                    }
                });

                // Show/hide the panel; remembered in a cookie so the server
                // renders the right state on the next load (no flash).
                function setTreeHidden(hidden, remember) {
                    layout.classList.toggle('is-tree-hidden', hidden);
                    toggleBtn.setAttribute('aria-pressed', hidden ? 'false' : 'true');
                    toggleBtn.classList.toggle('btn-primary-600', !hidden);
                    toggleBtn.classList.toggle('btn-outline-secondary-600', hidden);
                    if (remember) {
                        document.cookie = 'files_tree=' + (hidden ? 'hidden' : 'shown') + '; path=/; max-age=31536000; SameSite=Lax'
                            + (window.location.protocol === 'https:' ? '; Secure' : '');
                    }
                    if (!hidden) {
                        scrollActiveIntoView();
                    }
                }

                // On a phone the stacked tree would push the listing below the
                // fold, so it starts collapsed there until the user opts in.
                if (!/(?:^|;\s*)files_tree=/.test(document.cookie) && window.matchMedia('(max-width: 991.98px)').matches) {
                    setTreeHidden(true, false);
                }

                toggleBtn.addEventListener('click', function () {
                    setTreeHidden(!layout.classList.contains('is-tree-hidden'), true);
                });
                closeBtn.addEventListener('click', function () {
                    setTreeHidden(true, true);
                    toggleBtn.focus();
                });
            })();
        </script>
    @endpush

    @push('scripts')
        <script>
            (function () {
                document.getElementById('rename-modal').addEventListener('show.bs.modal', function (event) {
                    var trigger = event.relatedTarget;
                    document.getElementById('rename-form').action = trigger.getAttribute('data-action');
                    document.getElementById('rename_input').value = trigger.getAttribute('data-name');
                    document.getElementById('rename-label').textContent = trigger.getAttribute('data-label');
                });
                document.getElementById('move-modal').addEventListener('show.bs.modal', function (event) {
                    var trigger = event.relatedTarget;
                    document.getElementById('move-form').action = trigger.getAttribute('data-action');
                });
                document.getElementById('labels-modal').addEventListener('show.bs.modal', function (event) {
                    var trigger = event.relatedTarget;
                    var current = JSON.parse(trigger.getAttribute('data-label-ids') || '[]').map(String);
                    document.getElementById('labels-form').action = trigger.getAttribute('data-action');
                    document.querySelectorAll('#labels-form input[name="labels[]"]').forEach(function (box) {
                        box.checked = current.indexOf(box.value) !== -1;
                    });
                });

                // Right-click: reuse each card/row's own "..." dropdown-menu as a
                // context menu, positioned at the cursor, instead of duplicating
                // the action list in a separate menu structure.
                document.addEventListener('contextmenu', function (event) {
                    var host = event.target.closest('[data-context-menu]');
                    if (!host) {
                        return;
                    }
                    var menu = host.querySelector('.dropdown-menu');
                    if (!menu) {
                        return;
                    }
                    event.preventDefault();

                    document.querySelectorAll('.dropdown-menu.show').forEach(function (open) {
                        open.classList.remove('show');
                    });

                    menu.style.position = 'fixed';
                    menu.style.inset = 'auto';
                    menu.style.left = event.clientX + 'px';
                    menu.style.top = event.clientY + 'px';
                    menu.classList.add('show');

                    var close = function (closeEvent) {
                        if (menu.contains(closeEvent.target)) {
                            return;
                        }
                        menu.classList.remove('show');
                        menu.style.position = '';
                        menu.style.left = '';
                        menu.style.top = '';
                        document.removeEventListener('click', close);
                    };
                    document.addEventListener('click', close);
                });

                // --- Upload: XHR (for a real progress bar) + drag-and-drop ---
                var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                var uploadUrl = @json(route('files.entries.store'));
                var currentFolderId = @json($currentFolder?->id);

                function xhrUpload(formData, onProgress, onDone, onError) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', uploadUrl, true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            onProgress(Math.round((e.loaded / e.total) * 100));
                        }
                    });
                    xhr.addEventListener('load', function () {
                        if (xhr.status >= 200 && xhr.status < 400) {
                            onDone();
                            return;
                        }
                        var message = null;
                        try {
                            var data = JSON.parse(xhr.responseText);
                            if (data.errors) {
                                message = Object.values(data.errors).flat().join(' ');
                            } else if (data.message) {
                                message = data.message;
                            }
                        } catch (e) {}
                        onError(message);
                    });
                    xhr.addEventListener('error', function () {
                        onError(null);
                    });
                    xhr.send(formData);
                }

                // Modal upload: single file, progress bar inside the modal.
                var uploadForm = document.querySelector('[data-upload-form]');

                if (uploadForm) {
                    uploadForm.addEventListener('submit', function (event) {
                        event.preventDefault();

                        var submitBtn = uploadForm.querySelector('[data-upload-submit]');
                        var progressWrap = uploadForm.querySelector('[data-upload-progress-wrap]');
                        var progressBar = uploadForm.querySelector('[data-upload-progress-bar]');
                        var progressText = uploadForm.querySelector('[data-upload-progress-text]');
                        var errorBox = uploadForm.querySelector('[data-upload-error]');

                        errorBox.classList.add('d-none');
                        progressWrap.classList.remove('d-none');
                        submitBtn.disabled = true;

                        xhrUpload(new FormData(uploadForm), function (pct) {
                            progressBar.style.width = pct + '%';
                            progressText.textContent = pct + '%';
                        }, function () {
                            window.location.reload();
                        }, function (message) {
                            submitBtn.disabled = false;
                            progressWrap.classList.add('d-none');
                            errorBox.textContent = message || @json(__('files.upload_error_generic'));
                            errorBox.classList.remove('d-none');
                        });
                    });
                }

                // Drag-and-drop anywhere on the page.
                var dropOverlay = document.getElementById('drop-overlay');
                var dragDepth = 0;

                function isFileDrag(event) {
                    return event.dataTransfer && Array.prototype.indexOf.call(event.dataTransfer.types || [], 'Files') !== -1;
                }

                window.addEventListener('dragenter', function (event) {
                    if (!isFileDrag(event)) {
                        return;
                    }
                    dragDepth++;
                    dropOverlay.classList.remove('d-none');
                    dropOverlay.classList.add('d-flex');
                });

                window.addEventListener('dragover', function (event) {
                    if (isFileDrag(event)) {
                        event.preventDefault();
                    }
                });

                window.addEventListener('dragleave', function () {
                    dragDepth = Math.max(0, dragDepth - 1);
                    if (dragDepth === 0) {
                        dropOverlay.classList.add('d-none');
                        dropOverlay.classList.remove('d-flex');
                    }
                });

                window.addEventListener('drop', function (event) {
                    if (!isFileDrag(event)) {
                        return;
                    }
                    event.preventDefault();
                    dragDepth = 0;
                    dropOverlay.classList.add('d-none');
                    dropOverlay.classList.remove('d-flex');

                    var files = event.dataTransfer.files ? Array.from(event.dataTransfer.files) : [];
                    if (files.length) {
                        uploadDroppedFiles(files);
                    }
                });

                function uploadDroppedFiles(files) {
                    var toast = document.getElementById('upload-toast');
                    var toastBar = document.getElementById('upload-toast-bar');
                    var toastFilename = document.getElementById('upload-toast-filename');
                    var toastCount = document.getElementById('upload-toast-count');

                    toast.classList.remove('d-none');
                    var index = 0;

                    function next() {
                        if (index >= files.length) {
                            window.location.reload();
                            return;
                        }

                        var file = files[index];
                        toastFilename.textContent = file.name;
                        toastCount.textContent = (index + 1) + ' / ' + files.length;
                        toastBar.style.width = '0%';

                        var formData = new FormData();
                        formData.append('_token', csrfToken);
                        formData.append('file', file);
                        if (currentFolderId) {
                            formData.append('folder_id', currentFolderId);
                        }

                        xhrUpload(formData, function (pct) {
                            toastBar.style.width = pct + '%';
                        }, function () {
                            index++;
                            next();
                        }, function () {
                            index++;
                            next();
                        });
                    }

                    next();
                }

                // --- Drag-and-drop reordering: drag a file/folder onto a folder to move it. ---
                var fileMoveUrlTemplate = @json(route('files.entries.move', ['file' => '__ID__']));
                var folderMoveUrlTemplate = @json(route('files.folders.move', ['folder' => '__ID__']));
                var moveConfirmTextTemplate = @json(__('files.move_confirm_text'));

                var draggedItem = null;

                document.querySelectorAll('[data-drag-item]').forEach(function (el) {
                    el.setAttribute('draggable', 'true');

                    el.addEventListener('dragstart', function (event) {
                        // A click that starts on the "..." menu (or anything
                        // interactive inside it — rename, move, delete, share,
                        // color swatches...) must never be hijacked into a
                        // drag: a tiny mouse jitter during that click was
                        // otherwise enough to swallow the click entirely.
                        if (event.target.closest('.dropdown, form, button')) {
                            event.preventDefault();
                            return;
                        }

                        draggedItem = {
                            id: el.getAttribute('data-drag-id'),
                            type: el.getAttribute('data-drag-type'),
                            name: el.getAttribute('data-drag-name'),
                        };
                        event.dataTransfer.effectAllowed = 'move';
                        event.dataTransfer.setData('text/plain', draggedItem.id);
                        el.classList.add('opacity-50');
                    });

                    el.addEventListener('dragend', function () {
                        el.classList.remove('opacity-50');
                        draggedItem = null;
                    });
                });

                document.querySelectorAll('[data-drop-target]').forEach(function (el) {
                    el.addEventListener('dragover', function (event) {
                        if (!draggedItem) {
                            return;
                        }
                        var targetId = el.getAttribute('data-drop-id');
                        if (draggedItem.type === 'folder' && draggedItem.id === targetId) {
                            return;
                        }
                        event.preventDefault();
                        event.dataTransfer.dropEffect = 'move';
                        el.classList.add('border-primary-600');
                    });

                    el.addEventListener('dragleave', function () {
                        el.classList.remove('border-primary-600');
                    });

                    el.addEventListener('drop', function (event) {
                        event.preventDefault();
                        el.classList.remove('border-primary-600');

                        if (!draggedItem) {
                            return;
                        }

                        var targetId = el.getAttribute('data-drop-id');
                        var targetName = el.getAttribute('data-drop-name');

                        if (draggedItem.type === 'folder' && draggedItem.id === targetId) {
                            return;
                        }

                        var urlTemplate = draggedItem.type === 'folder' ? folderMoveUrlTemplate : fileMoveUrlTemplate;

                        document.getElementById('drag-move-form').action = urlTemplate.replace('__ID__', draggedItem.id);
                        document.getElementById('drag-move-parent-id').value = targetId || '';
                        document.getElementById('drag-move-confirm-text').textContent = moveConfirmTextTemplate
                            .replace(':name', draggedItem.name)
                            .replace(':destination', targetName);

                        var modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('drag-move-modal'));
                        modal.show();

                        draggedItem = null;
                    });
                });
            })();
        </script>
    @endpush
</x-app-layout>
