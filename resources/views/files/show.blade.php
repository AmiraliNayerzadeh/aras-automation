@php($users = $users ?? \App\Models\User::orderBy('name')->get(['id', 'name']))
@php($roles = $roles ?? \Spatie\Permission\Models\Role::orderBy('name')->get(['id', 'name']))

<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0 text-truncate">{{ $file->title ?: $file->original_name }}</h2>
            <div class="d-flex gap-2">
                @can('update', $file)
                    <button type="button" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm" data-bs-toggle="modal" data-bs-target="#share-modal-file-{{ $file->id }}">
                        <i class="ri-share-line"></i> {{ __('files.action_share') }}
                    </button>
                @endcan
                <a href="{{ route('files.entries.download', $file) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-download-2-line"></i> {{ __('files.action_download') }}
                </a>
                <a href="{{ route('files.index', ['folder' => $file->folder_id]) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-arrow-go-back-line"></i> {{ __('app.back') }}
                </a>
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('files.flash_'.str_replace('-', '_', session('status'))) }}</div>
    @endif

    <div class="row g-24">
        <div class="col-lg-8">
            <div class="card radius-12 mb-24">
                <div class="card-body">
                    <div class="bg-neutral-100 radius-8 d-flex align-items-center justify-content-center mb-16" style="min-height: 320px;">
                        @if ($file->isImage())
                            <img src="{{ asset('storage/'.$file->file_path) }}" alt="" class="w-100" style="max-height: 480px; object-fit: contain;">
                        @elseif ($file->isPdf())
                            <iframe src="{{ asset('storage/'.$file->file_path) }}" style="width: 100%; height: 480px; border: 0;"></iframe>
                        @elseif ($file->isSpreadsheet())
                            <div id="office-preview" class="w-100 bg-white radius-8 p-16 text-sm" style="max-height: 480px; overflow: auto;" data-preview-type="spreadsheet">
                                <div class="text-center text-secondary-light py-64" data-preview-loading>
                                    <i class="ri-loader-4-line" style="font-size: 32px;"></i>
                                    <p class="mt-8 mb-0">{{ __('files.preview_loading') }}</p>
                                </div>
                            </div>
                        @elseif ($file->isWordDocument())
                            <div id="office-preview" class="w-100 bg-white radius-8 p-16" style="max-height: 480px; overflow: auto;" data-preview-type="docx">
                                <div class="text-center text-secondary-light py-64" data-preview-loading>
                                    <i class="ri-loader-4-line" style="font-size: 32px;"></i>
                                    <p class="mt-8 mb-0">{{ __('files.preview_loading') }}</p>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-secondary-light py-64">
                                <i class="{{ $file->iconClass() }} {{ $file->iconColorClass() }}" style="font-size: 48px;"></i>
                                <p class="mt-8 mb-0">{{ __('files.preview_unavailable') }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-8">
                        <img src="{{ $file->owner?->avatar_url }}" alt="" class="w-24-px h-24-px rounded-circle object-fit-cover">
                        <span class="text-secondary-light text-sm">
                            {{ $file->owner?->name }} — {{ $file->created_at->format('Y-m-d H:i') }}
                        </span>
                    </div>
                    <div class="d-flex flex-wrap gap-16 text-secondary-light text-sm">
                        <span>{{ __('files.field_size') }}: {{ $file->human_size }}</span>
                        <span>{{ __('files.field_mime') }}: {{ $file->mime_type ?? '—' }}</span>
                        <span>{{ __('files.field_updated_at') }}: {{ $file->updated_at->format('Y-m-d H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="card radius-12">
                <div class="card-header bg-base fw-semibold">{{ __('files.notes_title') }}</div>
                <div class="card-body">
                    @forelse ($file->comments as $comment)
                        <div class="d-flex gap-12 mb-16">
                            <img src="{{ $comment->user?->avatar_url }}" alt="" class="w-32-px h-32-px rounded-circle object-fit-cover flex-shrink-0">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-8 mb-4">
                                    <span class="fw-semibold text-sm">{{ $comment->user?->name }}</span>
                                    <span class="text-secondary-light text-xs">{{ $comment->created_at->format('Y-m-d H:i') }}</span>
                                </div>
                                <p class="text-sm mb-0">{{ $comment->body }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-secondary-light text-sm mb-16">{{ __('files.notes_empty') }}</p>
                    @endforelse

                    <form method="POST" action="{{ route('files.entries.comments.store', $file) }}" class="mt-16">
                        @csrf
                        <textarea name="body" rows="2" class="form-control mb-2" placeholder="{{ __('files.field_note_body') }}" required>{{ old('body') }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mb-2" />
                        <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">
                            <i class="ri-send-plane-line"></i> {{ __('files.action_add_note') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card radius-12">
                <div class="card-header bg-base fw-semibold">{{ __('files.version_history_title') }}</div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between border-bottom pb-8 mb-8">
                        <div>
                            <div class="text-sm fw-medium">{{ $file->original_name }}</div>
                            <div class="text-secondary-light text-xs">{{ $file->human_size }} — {{ $file->updated_at->format('Y-m-d H:i') }}</div>
                        </div>
                        <span class="badge text-sm fw-semibold px-12 py-4 radius-4 text-success-600 bg-success-100">{{ __('files.version_current') }}</span>
                    </div>

                    @forelse ($file->versions as $version)
                        <div class="d-flex align-items-center justify-content-between border-bottom pb-8 mb-8">
                            <div>
                                <div class="text-sm">{{ $version->original_name }}</div>
                                <div class="text-secondary-light text-xs">{{ $version->uploadedBy?->name }} — {{ $version->created_at->format('Y-m-d H:i') }}</div>
                            </div>
                            @can('update', $file)
                                <form method="POST" action="{{ route('files.entries.version.restore', [$file, $version]) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-primary-600 radius-8 px-8 py-4">{{ __('files.action_restore_version') }}</button>
                                </form>
                            @endcan
                        </div>
                    @empty
                        <p class="text-secondary-light text-sm mb-0">{{ __('files.version_history_empty') }}</p>
                    @endforelse

                    @can('update', $file)
                        <form method="POST" action="{{ route('files.entries.version.store', $file) }}" enctype="multipart/form-data" class="mt-16">
                            @csrf
                            <input type="file" name="file" class="form-control mb-2" required>
                            <button type="submit" class="btn btn-outline-primary-600 radius-8 px-16 py-8 text-sm">{{ __('files.action_upload_version') }}</button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @can('update', $file)
        @include('files._share_modal', ['shareable' => $file, 'modalId' => 'share-modal-file-'.$file->id, 'storeRoute' => route('files.entries.shares.store', $file), 'destroyRouteBase' => 'files.entries.shares.destroy', 'destroyParam' => $file])
    @endcan

    @if ($file->isSpreadsheet())
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
            <script>
                (function () {
                    var container = document.getElementById('office-preview');

                    fetch(@json(route('files.entries.download', $file)))
                        .then(function (res) { return res.arrayBuffer(); })
                        .then(function (buffer) {
                            var workbook = XLSX.read(buffer, { type: 'array' });
                            var worksheet = workbook.Sheets[workbook.SheetNames[0]];
                            var html = XLSX.utils.sheet_to_html(worksheet, { editable: false });

                            container.innerHTML = html;

                            var table = container.querySelector('table');
                            if (table) {
                                table.classList.add('table', 'table-bordered', 'table-sm');
                            }

                            if (workbook.SheetNames.length > 1) {
                                var note = document.createElement('div');
                                note.className = 'text-secondary-light text-xs mt-8';
                                note.textContent = @json(__('files.preview_first_sheet_note'));
                                container.appendChild(note);
                            }
                        })
                        .catch(function () {
                            container.innerHTML = '<div class="text-center text-danger-600 py-4">'
                                + @json(__('files.preview_failed')) + '</div>';
                        });
                })();
            </script>
        @endpush
    @elseif ($file->isWordDocument())
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/docx-preview@0.3/dist/docx-preview.min.js"></script>
            <script>
                (function () {
                    var container = document.getElementById('office-preview');

                    fetch(@json(route('files.entries.download', $file)))
                        .then(function (res) { return res.blob(); })
                        .then(function (blob) {
                            container.innerHTML = '';
                            return docx.renderAsync(blob, container, null, { className: 'docx-preview' });
                        })
                        .catch(function () {
                            container.innerHTML = '<div class="text-center text-danger-600 py-4">'
                                + @json(__('files.preview_failed')) + '</div>';
                        });
                })();
            </script>
        @endpush
    @endif
</x-app-layout>
