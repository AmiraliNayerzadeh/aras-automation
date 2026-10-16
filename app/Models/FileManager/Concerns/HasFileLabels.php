<?php

namespace App\Models\FileManager\Concerns;

use App\Models\Settings\LookupValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasFileLabels
{
    /**
     * Labels are values of the "file_label" lookup type, so admins manage them
     * (names per language, colors, order, on/off) from the existing lookup UI.
     */
    public function labels(): MorphToMany
    {
        return $this->morphToMany(LookupValue::class, 'labelable', 'file_labelables', 'labelable_id', 'lookup_value_id')
            ->withTimestamps()
            ->orderBy('lookup_values.sort_order');
    }

    public function scopeWithLabel(Builder $query, int $lookupValueId): Builder
    {
        return $query->whereHas('labels', fn (Builder $q) => $q->where('lookup_values.id', $lookupValueId));
    }
}
