<?php

namespace App\Exports;

use App\Models\Tasks\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TasksExport implements FromQuery, WithHeadings, WithMapping
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(protected User $viewer, protected array $filters) {}

    public function query(): Builder
    {
        return Task::query()
            ->with(['assignees', 'createdBy', 'priority'])
            ->visibleTo($this->viewer)
            ->filter($this->filters)
            ->latest();
    }

    public function headings(): array
    {
        return [
            __('tasks.field_title'),
            __('tasks.field_created_by'),
            __('tasks.field_assigned_to'),
            __('tasks.field_priority'),
            __('tasks.field_due_date'),
            __('tasks.field_status'),
        ];
    }

    public function map($task): array
    {
        return [
            $task->title,
            $task->createdBy?->name,
            $task->assignees->pluck('name')->implode(', '),
            $task->priority ? ($task->priority->label[app()->getLocale()] ?? $task->priority->code) : null,
            $task->due_date?->format('Y-m-d'),
            __('tasks.status_'.$task->status->value),
        ];
    }
}
