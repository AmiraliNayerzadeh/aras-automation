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

#[Fillable(['company_id', 'name', 'code', 'address', 'phone', 'is_active'])]
class Branch extends Model
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
        return LogOptions::defaults()->logOnlyDirty()->useLogName('branch');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
