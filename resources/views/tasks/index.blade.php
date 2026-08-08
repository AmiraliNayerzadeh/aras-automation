<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h2 class="h4 fw-semibold mb-0">{{ __('tasks.title_index') }}</h2>
            <div class="d-flex gap-2">
                <div class="btn-group" role="group">
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'kanban']) }}" class="btn btn-sm radius-8 px-12 py-8 {{ $view === 'kanban' ? 'btn-primary-600' : 'btn-outline-secondary-600' }}" title="{{ __('tasks.view_kanban') }}">
                        <i class="ri-layout-column-line"></i>
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}" class="btn btn-sm radius-8 px-12 py-8 {{ $view === 'table' ? 'btn-primary-600' : 'btn-outline-secondary-600' }}" title="{{ __('tasks.view_table') }}">
                        <i class="ri-list-check-2"></i>
                    </a>
                </div>
                @if ($view === 'table')
                    <a href="{{ route('tasks.export.preview', request()->query()) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">
                        <i class="ri-download-2-line"></i> {{ __('exports.title') }}
                    </a>
                @endif
                <a href="{{ route('tasks.create') }}" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">
                    <i class="ri-add-line"></i> {{ __('app.create_new') }}
                </a>
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="alert alert-success radius-8 mb-24">{{ __('tasks.flash_'.str_replace('-', '_', str_replace('task-', '', session('status')))) }}</div>
    @endif

    <div class="card radius-12 mb-24">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="view" value="{{ $view }}">
                <div class="col-md-3">
                    <x-input-label for="search" :value="__('tasks.field_title')" />
                    <x-text-input id="search" name="search" class="mt-1 w-100" :value="request('search')" />
                </div>
                <div class="col-md-2">
                    <x-input-label for="assigned_to_id" :value="__('tasks.field_assigned_to')" />
                    <select id="assigned_to_id" name="assigned_to_id" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(request('assigned_to_id') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <x-input-label for="priority_lookup_value_id" :value="__('tasks.field_priority')" />
                    <select id="priority_lookup_value_id" name="priority_lookup_value_id" class="form-select radius-8 mt-1">
                        <option value="">—</option>
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority->id }}" @selected(request('priority_lookup_value_id') == $priority->id)>{{ $priority->label[app()->getLocale()] ?? $priority->code }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($view === 'table')
                    <div class="col-md-2">
                        <x-input-label for="status" :value="__('tasks.field_status')" />
                        <select id="status" name="status" class="form-select radius-8 mt-1">
                            <option value="">—</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ __('tasks.status_'.$status->value) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-2">
                    <x-input-label for="due_from" :value="__('tasks.field_due_from')" />
                    <x-text-input id="due_from" name="due_from" type="date" class="mt-1 w-100" :value="request('due_from')" />
                </div>
                <div class="col-md-2">
                    <x-input-label for="due_to" :value="__('tasks.field_due_to')" />
                    <x-text-input id="due_to" name="due_to" type="date" class="mt-1 w-100" :value="request('due_to')" />
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary-600 radius-8 px-16 py-8 text-sm">
                        <i class="ri-filter-3-line"></i> {{ __('app.actions') }}
                    </button>
                    <a href="{{ route('tasks.index', ['view' => $view]) }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
                </div>
            </form>
        </div>
    </div>

    @if ($view === 'kanban')
        <div class="kanban-wrapper d-flex align-items-start gap-24 overflow-x-auto" id="task-kanban">
            @foreach ($statuses as $status)
                <div class="flex-shrink-0" style="width: 300px;">
                    <div class="d-flex align-items-center justify-content-between mb-16">
                        <h6 class="mb-0">{{ __('tasks.status_'.$status->value) }}</h6>
                        <span class="badge text-sm fw-semibold px-12 py-4 radius-4 text-neutral-600 bg-neutral-200">{{ $tasksByStatus[$status->value]->count() }}</span>
                    </div>
                    <div class="connectedSortable bg-neutral-50 radius-12 p-12" style="min-height: 200px;" data-status="{{ $status->value }}">
                        @forelse ($tasksByStatus[$status->value] as $task)
                            <div class="kanban-card bg-base border p-16 radius-8 mb-12 shadow-sm" data-task-id="{{ $task->id }}" style="cursor: grab;">
                                <div class="d-flex justify-content-between align-items-start mb-8">
                                    <a href="{{ route('tasks.show', $task) }}" class="kanban-title text-md fw-semibold text-primary-light">{{ $task->title }}</a>
                                </div>
                                @if ($task->description)
                                    <p class="text-secondary-light text-sm mb-8 text-truncate">{{ $task->description }}</p>
                                @endif
                                <div class="text-secondary-light text-xs mb-8">
                                    <i class="ri-user-add-line"></i> {{ $task->createdBy?->name }}
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($task->priority)
                                            <span class="badge text-xs fw-semibold px-8 py-2 radius-4 text-{{ $task->priority->color }}-600 bg-{{ $task->priority->color }}-100">
                                                {{ $task->priority->label[app()->getLocale()] ?? $task->priority->code }}
                                            </span>
                                        @endif
                                        @if ($task->due_date)
                                            <span class="text-secondary-light text-xs"><i class="ri-calendar-line"></i> {{ $task->due_date->format('M d') }}</span>
                                        @endif
                                    </div>
                                    <div class="d-flex align-items-center" style="margin-inline-start: auto;">
                                        @forelse ($task->assignees as $assignee)
                                            <img src="{{ $assignee->avatar_url }}" alt="{{ $assignee->name }}" title="{{ $assignee->name }}" class="w-24-px h-24-px rounded-circle object-fit-cover border border-white" style="margin-inline-start: -8px;">
                                        @empty
                                            <span class="text-secondary-light text-xs">{{ __('tasks.unassigned') }}</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-secondary-light text-sm text-center mb-0 py-16">{{ __('tasks.kanban_empty') }}</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>

        @push('scripts')
            <script src="{{ asset('assets/wowdash/js/lib/jquery-ui.min.js') }}"></script>
            <script>
                (function () {
                    $('.connectedSortable').sortable({
                        connectWith: '.connectedSortable',
                        placeholder: 'radius-8 border border-dashed mb-12',
                        stop: function (event, ui) {
                            var taskId = ui.item.data('task-id');
                            var newStatus = ui.item.closest('.connectedSortable').data('status');

                            fetch('{{ url('tasks') }}/' + taskId + '/status', {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({ status: newStatus })
                            });
                        }
                    }).disableSelection();
                })();
            </script>
        @endpush
    @else
        <div class="card radius-12">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('tasks.field_title') }}</th>
                            <th>{{ __('tasks.field_created_by') }}</th>
                            <th>{{ __('tasks.field_assigned_to') }}</th>
                            <th>{{ __('tasks.field_priority') }}</th>
                            <th>{{ __('tasks.field_due_date') }}</th>
                            <th>{{ __('tasks.field_status') }}</th>
                            <th class="text-end">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tasks as $task)
                            @php
                                $statusColor = match ($task->status->value) {
                                    'done' => 'text-success-600 bg-success-100',
                                    'cancelled' => 'text-neutral-600 bg-neutral-200',
                                    'in_progress' => 'text-warning-600 bg-warning-100',
                                    default => 'text-info-600 bg-info-100',
                                };
                            @endphp
                            <tr>
                                <td><a href="{{ route('tasks.show', $task) }}" class="text-primary-light fw-medium">{{ $task->title }}</a></td>
                                <td>{{ $task->createdBy?->name }}</td>
                                <td>
                                    @forelse ($task->assignees as $assignee)
                                        <div class="d-flex align-items-center gap-2 mb-4">
                                            <img src="{{ $assignee->avatar_url }}" alt="" class="w-24-px h-24-px rounded-circle object-fit-cover">
                                            {{ $assignee->name }}
                                        </div>
                                    @empty
                                        <span class="text-secondary-light">{{ __('tasks.unassigned') }}</span>
                                    @endforelse
                                </td>
                                <td>
                                    @if ($task->priority)
                                        <span class="badge text-sm fw-semibold px-12 py-4 radius-4 text-{{ $task->priority->color }}-600 bg-{{ $task->priority->color }}-100">
                                            {{ $task->priority->label[app()->getLocale()] ?? $task->priority->code }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $task->due_date?->format('Y-m-d') ?? '—' }}</td>
                                <td>
                                    <span class="badge text-sm fw-semibold px-12 py-4 radius-4 {{ $statusColor }}">
                                        {{ __('tasks.status_'.$task->status->value) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-primary-600 btn-sm radius-8 px-12 py-6 text-sm">{{ __('tasks.action_view') }}</a>
                                    <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-secondary-600 btn-sm radius-8 px-12 py-6 text-sm">{{ __('app.edit') }}</a>
                                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('tasks.confirm_delete') }}');">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-outline-danger-600 btn-sm radius-8 px-12 py-6 text-sm">{{ __('app.delete') }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">{{ __('app.no_records') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-24">
            {{ $tasks->links() }}
        </div>
    @endif
</x-app-layout>
