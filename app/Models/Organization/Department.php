<?php

namespace App\Models\Organization;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['branch_id', 'name', 'code', 'is_active'])]
class Department extends Model
{
    use LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('department');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
