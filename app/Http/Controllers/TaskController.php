<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Models\Document;
use App\Models\Settings\LookupValue;
use App\Models\Tasks\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Task::class);

        $query = Task::query()
            ->with(['assignees', 'createdBy', 'priority'])
            ->visibleTo($request->user())
            ->filter(static::filtersFromRequest($request));

        $view = $request->input('view') === 'table' ? 'table' : 'kanban';

        if ($view === 'kanban') {
            $tasks = (clone $query)->orderByDesc('id')->limit(200)->get();

            return view('tasks.index', [
                'view' => $view,
                'tasksByStatus' => collect(TaskStatus::cases())->mapWithKeys(
                    fn (TaskStatus $status) => [$status->value => $tasks->where('status', $status)]
                ),
            ] + $this->formOptions());
        }

        $tasks = $query->latest()->paginate(20)->withQueryString();

        return view('tasks.index', ['view' => $view, 'tasks' => $tasks] + $this->formOptions());
    }

    public function create(): View
    {
        $this->authorize('create', Task::class);

        return view('tasks.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Task::class);

        $data = $this->validateBaseData($request);
        $assigneeIds = $data['assigned_to_ids'] ?? [];
        unset($data['assigned_to_ids']);

        $task = Task::create($data + [
            'status' => TaskStatus::Todo->value,
            'created_by_id' => $request->user()->id,
        ]);

        $task->assignees()->sync($assigneeIds);

        return redirect()->route('tasks.show', $task)->with('status', 'task-created');
    }

    public function show(Task $task): View
    {
        $this->authorize('view', $task);

        $task->load(['assignees', 'createdBy', 'priority', 'documents.uploadedBy', 'comments.user']);

        return view('tasks.show', ['task' => $task]);
    }

    public function edit(Task $task): View
    {
        $this->authorize('update', $task);

        return view('tasks.edit', ['task' => $task->load('assignees')] + $this->formOptions());
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $data = $this->validateBaseData($request) + $request->validate([
            'status' => ['required', Rule::in(array_column(TaskStatus::cases(), 'value'))],
        ]);
        $assigneeIds = $data['assigned_to_ids'] ?? [];
        unset($data['assigned_to_ids']);
        $data['completed_at'] = $data['status'] === TaskStatus::Done->value ? ($task->completed_at ?? now()) : null;

        $task->update($data);
        $task->assignees()->sync($assigneeIds);

        return redirect()->route('tasks.show', $task)->with('status', 'task-updated');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return redirect()->route('tasks.index')->with('status', 'task-deleted');
    }

    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'status' => ['required', Rule::in(array_column(TaskStatus::cases(), 'value'))],
        ]);

        $task->update([
            'status' => $data['status'],
            'completed_at' => $data['status'] === TaskStatus::Done->value ? now() : null,
        ]);

        return redirect()->route('tasks.index')->with('status', 'task-updated');
    }

    public function storeComment(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('view', $task);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $task->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return redirect()->route('tasks.show', $task)->with('status', 'task-commentadded');
    }

    public function storeDocument(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('view', $task);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->store("documents/task/{$task->id}", 'public');

        $task->documents()->create([
            'title' => $data['title'] ?? null,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size_bytes' => $file->getSize(),
            'uploaded_by_id' => $request->user()->id,
        ]);

        return redirect()->route('tasks.show', $task)->with('status', 'task-attachmentadded');
    }

    public function destroyDocument(Request $request, Task $task, Document $document): RedirectResponse
    {
        abort_unless($document->documentable_id === $task->id && $document->documentable_type === 'task', 404);
        $this->authorize('manageAttachment', [$task, $document->uploaded_by_id]);

        $document->delete();

        return redirect()->route('tasks.show', $task)->with('status', 'task-attachmentdeleted');
    }

    /**
     * @return array<string, mixed>
     */
    public static function filtersFromRequest(Request $request): array
    {
        return [
            'search' => $request->string('search')->toString(),
            'assigned_to_id' => $request->input('assigned_to_id'),
            'status' => $request->input('status'),
            'priority_lookup_value_id' => $request->input('priority_lookup_value_id'),
            'due_from' => $request->input('due_from'),
            'due_to' => $request->input('due_to'),
        ];
    }

    protected function validateBaseData(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority_lookup_value_id' => ['nullable', 'exists:lookup_values,id'],
            'assigned_to_ids' => ['nullable', 'array'],
            'assigned_to_ids.*' => ['exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ]);
    }

    protected function formOptions(): array
    {
        return [
            'users' => User::orderBy('name')->get(['id', 'name']),
            'priorities' => LookupValue::whereHas('lookupType', fn ($q) => $q->where('code', 'task_priority'))
                ->where('is_active', true)->orderBy('sort_order')->get(),
            'statuses' => TaskStatus::cases(),
        ];
    }
}
