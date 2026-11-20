<?php

namespace App\Models\Merchandising;

use App\Models\FileManager\Folder;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'submission_key', 'user_id', 'store_id', 'visited_at', 'divisions', 'actions',
    'store_feedback', 'needs_order', 'note', 'folder_id',
    'latitude', 'longitude', 'location_accuracy', 'distance_m', 'location_status',
])]
class Visit extends Model
{
    use SoftDeletes;

    protected $table = 'merchandising_visits';

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
            'divisions' => 'array',
            'actions' => 'array',
            'store_feedback' => 'array',
            'needs_order' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class)->withTrashed();
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VisitItem::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(Upload::class)->whereNotNull('file_entry_id')->orderBy('id');
    }

    public function mapUrl(): ?string
    {
        return $this->latitude !== null ? 'https://www.google.com/maps?q='.$this->latitude.','.$this->longitude : null;
    }

    /**
     * Visits a reviewer hasn't seen yet: anyone else's, submitted since they
     * last opened the visits list.
     */
    public static function unseenCountFor(User $user): int
    {
        return static::where('user_id', '!=', $user->id)
            ->when($user->merchandising_visits_seen_at, fn ($q, $seen) => $q->where('created_at', '>', $seen))
            ->count();
    }

    /**
     * Admins see everything; a merchandiser only their own visits.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->can('merchandising.view_all') ? $query : $query->where('user_id', $user->id);
    }
}
