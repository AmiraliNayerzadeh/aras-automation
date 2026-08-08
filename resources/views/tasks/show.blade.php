<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('tasks.title_detail') }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-edit-line"></i> {{ __('app.edit') }}
                </a>
                <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-arrow-go-back-line"></i> {{ __('app.back') }}
                </a>
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('tasks.flash_'.str_replace('-', '_', str_replace('task-', '', session('status')))) }}</div>
    @endif

    <div class="row g-24">
        <div class="col-lg-8">
            <div class="card radius-12 mb-24">
                <div class="card-body">
                    @php
                        $statusColor = match ($task->status->value) {
                            'done' => 'text-success-600 bg-success-100',
                            'cancelled' => 'text-neutral-600 bg-neutral-200',
                            'in_progress' => 'text-warning-600 bg-warning-100',
                            default => 'text-info-600 bg-info-100',
                        };
                    @endphp
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-12">
                        <h4 class="mb-0">{{ $task->title }}</h4>
                        <span class="badge text-sm fw-semibold px-12 py-4 radius-4 {{ $statusColor }}">
                            {{ __('tasks.status_'.$task->status->value) }}
                        </span>
                    </div>

                    @if ($task->description)
                        <p class="text-secondary-light mb-16">{{ $task->description }}</p>
                    @endif

                    <div class="d-flex flex-wrap gap-16 text-secondary-light text-sm mb-16">
                        @if ($task->priority)
                            <span class="badge text-xs fw-semibold px-8 py-2 radius-4 text-{{ $task->priority->color }}-600 bg-{{ $task->priority->color }}-100">
                                {{ $task->priority->label[app()->getLocale()] ?? $task->priority->code }}
                            </span>
                        @endif
                        @if ($task->due_date)
                            <span><i class="ri-calendar-line"></i> {{ $task->due_date->format('Y-m-d') }}</span>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-2 mb-8">
                        <img src="{{ $task->createdBy?->avatar_url }}" alt="" class="w-24-px h-24-px rounded-circle object-fit-cover">
                        <span class="text-secondary-light text-sm">
                            {{ $task->createdBy?->name }}
                            — {{ __('tasks.created_on', ['date' => $task->created_at->format('Y-m-d H:i')]) }}
                        </span>
                    </div>

                    <div>
                        <div class="text-secondary-light text-sm mb-4">{{ __('tasks.field_assigned_to') }}</div>
                        <div class="d-flex flex-wrap gap-8">
                            @forelse ($task->assignees as $assignee)
                                <span class="d-flex align-items-center gap-2 border radius-8 px-8 py-4">
                                    <img src="{{ $assignee->avatar_url }}" alt="" class="w-20-px h-20-px rounded-circle object-fit-cover">
                                    <span class="text-sm">{{ $assignee->name }}</span>
                                </span>
                            @empty
                                <span class="text-secondary-light text-sm">{{ __('tasks.unassigned') }}</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="card radius-12">
                <div class="card-header bg-base fw-semibold">{{ __('tasks.notes_title') }}</div>
                <div class="card-body">
                    @forelse ($task->comments as $comment)
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
                        <p class="text-secondary-light text-sm mb-16">{{ __('tasks.notes_empty') }}</p>
                    @endforelse

                    <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="mt-16">
                        @csrf
                        <textarea name="body" rows="2" class="form-control mb-2" placeholder="{{ __('tasks.field_note_body') }}" required>{{ old('body') }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mb-2" />
                        <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">
                            <i class="ri-send-plane-line"></i> {{ __('tasks.action_add_note') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card radius-12">
                <div class="card-header bg-base fw-semibold">{{ __('tasks.attachments_title') }}</div>
                <div class="card-body">
                    @forelse ($task->documents as $document)
                        <div class="d-flex align-items-center justify-content-between border-bottom pb-8 mb-8">
                            <a href="{{ asset('storage/'.$document->file_path) }}" target="_blank" class="text-primary-light text-sm text-truncate">
                                <i class="ri-file-line"></i> {{ $document->title ?: $document->original_name }}
                            </a>
                            @can('manageAttachment', [$task, $document->uploaded_by_id])
                                <form method="POST" action="{{ route('tasks.documents.destroy', [$task, $document]) }}" onsubmit="return confirm('{{ __('tasks.confirm_delete_attachment') }}');">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-sm btn-outline-danger-600 radius-8 px-8 py-4">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    @empty
                        <p class="text-secondary-light text-sm mb-16">{{ __('tasks.attachments_empty') }}</p>
                    @endforelse

                    <form method="POST" action="{{ route('tasks.documents.store', $task) }}" enctype="multipart/form-data" class="mt-16">
                        @csrf
                        <div class="mb-2">
                            <x-input-label for="attachment_title" :value="__('tasks.field_attachment_title')" />
                            <x-text-input id="attachment_title" name="title" class="mt-1 w-100" :value="old('title')" />
                        </div>
                        <div class="mb-2">
                            <x-input-label for="attachment_file" :value="__('tasks.field_attachment_file')" />
                            <input type="file" id="attachment_file" name="file" class="form-control mt-1" required>
                            <x-input-error :messages="$errors->get('file')" class="mt-1" />
                        </div>
                        <button type="submit" class="btn btn-outline-primary-600 radius-8 px-16 py-8 text-sm">
                            <i class="ri-attachment-2"></i> {{ __('tasks.action_attach') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
