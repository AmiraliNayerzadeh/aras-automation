<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable(['key', 'value', 'type', 'group', 'is_public', 'description'])]
class Setting extends Model
{
    use LogsActivity, SoftDeletes;

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty()->useLogName('setting');
    }
}
