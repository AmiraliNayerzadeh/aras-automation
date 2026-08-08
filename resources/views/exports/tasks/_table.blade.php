<table class="table table-hover align-middle mb-0">
    <thead>
        <tr>
            <th>{{ __('tasks.field_title') }}</th>
            <th>{{ __('tasks.field_created_by') }}</th>
            <th>{{ __('tasks.field_assigned_to') }}</th>
            <th>{{ __('tasks.field_priority') }}</th>
            <th>{{ __('tasks.field_due_date') }}</th>
            <th>{{ __('tasks.field_status') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($tasks as $task)
            <tr>
                <td>{{ $task->title }}</td>
                <td>{{ $task->createdBy?->name }}</td>
                <td>{{ $task->assignees->pluck('name')->implode(', ') ?: __('tasks.unassigned') }}</td>
                <td>{{ $task->priority ? ($task->priority->label[app()->getLocale()] ?? $task->priority->code) : '—' }}</td>
                <td>{{ $task->due_date?->format('Y-m-d') ?? '—' }}</td>
                <td>{{ __('tasks.status_'.$task->status->value) }}</td>
            </tr>
        @empty
            <tr><td colspan="6">{{ __('exports.no_records') }}</td></tr>
        @endforelse
    </tbody>
</table>
