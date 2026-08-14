<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('files.title_index') }}</h2>
            <div class="d-flex gap-2">
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

    @unless ($currentFolder)
        <ul class="nav nav-pills mb-24">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'mine' ? 'active' : '' }}" href="{{ route('files.index', ['tab' => 'mine']) }}">{{ __('files.tab_mine') }}</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'shared' ? 'active' : '' }}" href="{{ route('files.index', ['tab' => 'shared']) }}">{{ __('files.tab_shared') }}</a>
            </li>
            @can('files.view_all')
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'all' ? 'active' : '' }}" href="{{ route('files.index', ['tab' => 'all']) }}">{{ __('files.tab_all') }}</a>
                </li>
            @endcan
        </ul>
    @else
        <nav class="mb-24">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('files.index', ['tab' => $tab]) }}">{{ __('files.breadcrumb_home') }}</a></li>
                @foreach ($breadcrumb as $crumb)
                    <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                        @if ($loop->last || ! auth()->user()->can('view', $crumb))
                            {{ $crumb->name }}
                        @else
                            <a href="{{ route('files.index', ['folder' => $crumb->id]) }}">{{ $crumb->name }}</a>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    @endunless

    @if ($folders->isEmpty() && $files->isEmpty())
        <div class="card radius-12">
            <div class="card-body text-center text-muted py-4">{{ __('files.empty_folder') }}</div>
        </div>
    @else
        <div class="row g-16">
            @foreach ($folders as $folder)
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="border radius-12 p-16 h-100 bg-base position-relative">
                        <div class="dropdown position-absolute top-0 end-0 me-8 mt-8">
                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false" class="w-32-px h-32-px radius-8 border d-flex justify-content-center align-items-center bg-base">
                                <i class="ri-more-2-fill"></i>
                            </button>
                            <ul class="dropdown-menu p-8 border bg-base shadow">
                                <li><a class="dropdown-item px-12 py-6 rounded text-secondary-light" href="{{ route('files.index', ['folder' => $folder->id]) }}">{{ __('files.action_open') }}</a></li>
                                @can('update', $folder)
                                    <li>
                                        <button type="button" class="dropdown-item px-12 py-6 rounded text-secondary-light w-100 text-start border-0 bg-transparent"
                                            data-bs-toggle="modal" data-bs-target="#rename-modal"
                                            data-action="{{ route('files.folders.update', $folder) }}" data-name="{{ $folder->name }}" data-label="{{ __('files.field_folder_name') }}">
                                            {{ __('files.action_rename') }}
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item px-12 py-6 rounded text-secondary-light w-100 text-start border-0 bg-transparent"
                                            data-bs-toggle="modal" data-bs-target="#move-modal" data-action="{{ route('files.folders.move', $folder) }}">
                                            {{ __('files.action_move') }}
                                        </button>
                                    </li>
                                    <li><a class="dropdown-item px-12 py-6 rounded text-secondary-light" href="#" data-bs-toggle="modal" data-bs-target="#share-modal-folder-{{ $folder->id }}">{{ __('files.action_share') }}</a></li>
                                    <li>
                                        <form action="{{ route('files.folders.destroy', $folder) }}" method="POST" onsubmit="return confirm('{{ __('files.confirm_delete_folder') }}');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="dropdown-item px-12 py-6 rounded text-danger-600 w-100 text-start border-0 bg-transparent">{{ __('files.action_delete') }}</button>
                                        </form>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                        <a href="{{ route('files.index', ['folder' => $folder->id]) }}" class="d-flex flex-column align-items-center text-center text-decoration-none py-16">
                            <i class="ri-folder-3-fill text-warning-main" style="font-size: 48px;"></i>
                            <span class="text-primary-light fw-medium text-sm mt-8 text-truncate w-100">{{ $folder->name }}</span>
                            <span class="text-secondary-light text-xs">{{ $folder->owner?->name }}</span>
                        </a>
                    </div>
                </div>
                @can('update', $folder)
                    @include('files._share_modal', ['shareable' => $folder, 'modalId' => 'share-modal-folder-'.$folder->id, 'storeRoute' => route('files.folders.shares.store', $folder), 'destroyRouteBase' => 'files.folders.shares.destroy', 'destroyParam' => $folder])
                @endcan
            @endforeach

            @foreach ($files as $file)
                <div class="col-xl-3 col-lg-4 col-sm-6">
                    <div class="border radius-12 h-100 bg-base position-relative overflow-hidden">
                        <div class="dropdown position-absolute top-0 end-0 me-8 mt-8 z-1">
                            <button type="button" data-bs-toggle="dropdown" aria-expanded="false" class="w-32-px h-32-px radius-8 border d-flex justify-content-center align-items-center bg-base">
                                <i class="ri-more-2-fill"></i>
                            </button>
                            <ul class="dropdown-menu p-8 border bg-base shadow">
                                <li><a class="dropdown-item px-12 py-6 rounded text-secondary-light" href="{{ route('files.entries.show', $file) }}">{{ __('files.action_preview') }}</a></li>
                                <li><a class="dropdown-item px-12 py-6 rounded text-secondary-light" href="{{ route('files.entries.download', $file) }}">{{ __('files.action_download') }}</a></li>
                                @can('update', $file)
                                    <li>
                                        <button type="button" class="dropdown-item px-12 py-6 rounded text-secondary-light w-100 text-start border-0 bg-transparent"
                                            data-bs-toggle="modal" data-bs-target="#rename-modal"
                                            data-action="{{ route('files.entries.update', $file) }}" data-name="{{ $file->title ?: $file->original_name }}" data-label="{{ __('files.field_title') }}">
                                            {{ __('files.action_rename') }}
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item px-12 py-6 rounded text-secondary-light w-100 text-start border-0 bg-transparent"
                                            data-bs-toggle="modal" data-bs-target="#move-modal" data-action="{{ route('files.entries.move', $file) }}">
                                            {{ __('files.action_move') }}
                                        </button>
                                    </li>
                                    <li><a class="dropdown-item px-12 py-6 rounded text-secondary-light" href="#" data-bs-toggle="modal" data-bs-target="#share-modal-file-{{ $file->id }}">{{ __('files.action_share') }}</a></li>
                                @endcan
                                @can('delete', $file)
                                    <li>
                                        <form action="{{ route('files.entries.destroy', $file) }}" method="POST" onsubmit="return confirm('{{ __('files.confirm_delete_file') }}');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="dropdown-item px-12 py-6 rounded text-danger-600 w-100 text-start border-0 bg-transparent">{{ __('files.action_delete') }}</button>
                                        </form>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                        <a href="{{ route('files.entries.show', $file) }}" class="d-block bg-neutral-100 d-flex align-items-center justify-content-center" style="height: 120px;">
                            @if ($file->isImage())
                                <img src="{{ asset('storage/'.$file->file_path) }}" alt="" class="w-100 h-100 object-fit-cover">
                            @elseif ($file->isPdf())
                                <i class="ri-file-pdf-2-line text-danger-main" style="font-size: 40px;"></i>
                            @else
                                <i class="ri-file-3-line text-neutral-400" style="font-size: 40px;"></i>
                            @endif
                        </a>
                        <div class="p-12">
                            <div class="text-primary-light fw-medium text-sm text-truncate" title="{{ $file->title ?: $file->original_name }}">
                                <a href="{{ route('files.entries.show', $file) }}" class="text-primary-light">{{ $file->title ?: $file->original_name }}</a>
                            </div>
                            <div class="text-secondary-light text-xs d-flex justify-content-between mt-4">
                                <span>{{ $file->owner?->name }}</span>
                                <span>{{ $file->human_size }}</span>
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
            <form method="POST" action="{{ route('files.entries.store') }}" enctype="multipart/form-data" class="modal-content">
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm" data-bs-dismiss="modal">{{ __('app.cancel') }}</button>
                    <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">{{ __('files.action_upload') }}</button>
                </div>
            </form>
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
            })();
        </script>
    @endpush
</x-app-layout>
