<?php

namespace App\Models\Tasks;

use App\Enums\TaskStatus;
use App\Models\Document;
use App\Models\Settings\LookupValue;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable([
    'title', 'description', 'priority_lookup_value_id', 'status',
    'due_date', 'completed_at', 'created_by_id',
])]
class Task extends Model
{
    use LogsActivity, SoftDeletes;

    protected $attributes = [
        'status' => 'todo',
    ];

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'due_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('task');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(LookupValue::class, 'priority_lookup_value_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->latest();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->can('tasks.view_all')) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->whereHas('assignees', fn ($qq) => $qq->where('users.id', $user->id))
                ->orWhere('created_by_id', $user->id);
        });
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->when($filters['assigned_to_id'] ?? null, fn ($q, $value) => $q->whereHas('assignees', fn ($qq) => $qq->where('users.id', $value)))
            ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))
            ->when($filters['priority_lookup_value_id'] ?? null, fn ($q, $value) => $q->where('priority_lookup_value_id', $value))
            ->when($filters['due_from'] ?? null, fn ($q, $value) => $q->whereDate('due_date', '>=', $value))
            ->when($filters['due_to'] ?? null, fn ($q, $value) => $q->whereDate('due_date', '<=', $value));
    }
}
