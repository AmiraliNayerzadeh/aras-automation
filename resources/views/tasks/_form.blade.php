@php($task = $task ?? null)

<div class="row g-3">
    <div class="col-12">
        <x-input-label for="title" :value="__('tasks.field_title')" />
        <x-text-input id="title" name="title" class="mt-1 w-100" :value="old('title', $task?->title)" required autofocus />
        <x-input-error :messages="$errors->get('title')" class="mt-1" />
    </div>

    <div class="col-12">
        <x-input-label for="description" :value="__('tasks.field_description')" />
        <textarea id="description" name="description" rows="3" class="form-control mt-1">{{ old('description', $task?->description) }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-1" />
    </div>

    @php($selectedAssigneeIds = old('assigned_to_ids', $task?->assignees->pluck('id')->all() ?? []))
    <div class="col-md-4">
        <x-input-label for="assigned_to_ids" :value="__('tasks.field_assigned_to')" />
        <select id="assigned_to_ids" name="assigned_to_ids[]" class="form-select mt-1" multiple size="4">
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected(in_array($user->id, $selectedAssigneeIds))>{{ $user->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('assigned_to_ids')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="priority_lookup_value_id" :value="__('tasks.field_priority')" />
        <select id="priority_lookup_value_id" name="priority_lookup_value_id" class="form-select mt-1">
            <option value="">—</option>
            @foreach ($priorities as $priority)
                <option value="{{ $priority->id }}" @selected(old('priority_lookup_value_id', $task?->priority_lookup_value_id) == $priority->id)>{{ $priority->label[app()->getLocale()] ?? $priority->code }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('priority_lookup_value_id')" class="mt-1" />
    </div>

    <div class="col-md-4">
        <x-input-label for="due_date" :value="__('tasks.field_due_date')" />
        <x-text-input id="due_date" name="due_date" type="date" class="mt-1 w-100" :value="old('due_date', $task?->due_date?->format('Y-m-d'))" />
        <x-input-error :messages="$errors->get('due_date')" class="mt-1" />
    </div>

    @if ($task)
        <div class="col-md-4">
            <x-input-label for="status" :value="__('tasks.field_status')" />
            <select id="status" name="status" class="form-select mt-1" required>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $task->status->value) === $status->value)>{{ __('tasks.status_'.$status->value) }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-1" />
        </div>
    @endif
</div>

<div class="mt-24 d-flex gap-2">
    <x-primary-button>{{ __('app.save') }}</x-primary-button>
    <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary-600 radius-8 px-16 py-8 text-sm">{{ __('app.cancel') }}</a>
</div>
