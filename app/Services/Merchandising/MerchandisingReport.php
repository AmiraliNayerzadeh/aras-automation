<?php

namespace App\Services\Merchandising;

use App\Models\Merchandising\Visit;
use App\Models\Merchandising\VisitItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Everything the merchandising report shows, for one day or a date range,
 * optionally narrowed to a division, merchandiser, store type, region or store.
 * With a division picked, product figures cover only that division, so a
 * report for someone who handles TOP SUN alone reads complete on its own.
 */
class MerchandisingReport
{
    public const OUT_STATUSES = ['out_of_stock', 'not_listed'];

    /** @var Collection<int, Visit> */
    public Collection $visits;

    /** @var Collection<int, VisitItem> */
    public Collection $items;

    public Carbon $from;

    public Carbon $to;

    /** @var array<string, mixed> */
    public array $filters;

    public function __construct(private readonly MerchandisingOptions $options) {}

    public function build(Request $request): self
    {
        // Visit times are the company's wall-clock time, like "today" here.
        $this->from = Carbon::parse($request->input('date_from') ?: $this->options->now()->toDateString())->startOfDay();
        $this->to = $request->filled('date_to') ? Carbon::parse($request->input('date_to'))->endOfDay() : $this->from->copy()->endOfDay();
        $this->filters = $request->only(['division', 'user_id', 'store_type', 'region', 'store_id']);

        $division = $this->filters['division'] ?? null;

        $this->visits = Visit::query()
            ->with(['store', 'user', 'items.product', 'media.fileEntry'])
            ->whereBetween('visited_at', [$this->from, $this->to])
            ->when($this->filters['user_id'] ?? null, fn ($q, $id) => $q->where('user_id', $id))
            ->when($this->filters['store_id'] ?? null, fn ($q, $id) => $q->where('store_id', $id))
            ->when($this->filters['store_type'] ?? null, fn ($q, $type) => $q->whereHas('store', fn ($s) => $s->where('type', $type)))
            ->when($this->filters['region'] ?? null, fn ($q, $region) => $q->whereHas('store', fn ($s) => $s->where('region', $region)))
            ->when($division, fn ($q) => $q->whereHas('items', fn ($i) => $i->where('division', $division)))
            ->orderBy('visited_at')
            ->get();

        $this->items = $this->visits->flatMap(fn (Visit $v) => $v->items)
            ->when($division, fn ($items) => $items->where('division', $division))
            ->values();

        return $this;
    }

    /**
     * @return array<string, int|float|null>
     */
    public function kpis(): array
    {
        $checked = $this->items->count();
        $out = $this->items->whereIn('stock_status', self::OUT_STATUSES)->count();
        $tagged = $this->items->whereNotNull('price_tag');

        return [
            'visits' => $this->visits->count(),
            'stores' => $this->visits->pluck('store_id')->filter()->unique()->count(),
            'merchandisers' => $this->visits->pluck('user_id')->unique()->count(),
            'products_checked' => $checked,
            'out_of_stock' => $out,
            'out_of_stock_rate' => $checked ? round($out / $checked * 100, 1) : null,
            'price_tag_ok_rate' => $tagged->count() ? round($tagged->where('price_tag', 'correct')->count() / $tagged->count() * 100, 1) : null,
            'items_with_issues' => $this->items->filter(fn ($i) => ! empty($i->issues))->count(),
            'photos' => $this->visits->sum(fn (Visit $v) => $v->media->count()),
            'orders_needed' => $this->items->where('needs_order', true)->count(),
        ];
    }

    /**
     * @param  list<string>  $keys
     * @return array<string, int> every key, in order, even when zero
     */
    public function distribution(string $field, array $keys): array
    {
        $counts = $this->items->countBy($field);

        return collect($keys)->mapWithKeys(fn ($key) => [$key => (int) ($counts[$key] ?? 0)])->all();
    }

    /**
     * Counts across a JSON list column (issues on items; actions or feedback on visits).
     *
     * @return array<string, int> code => count, most frequent first
     */
    public function tally(string $field, bool $onVisits = false): array
    {
        return ($onVisits ? $this->visits : $this->items)
            ->flatMap(fn ($row) => (array) $row->{$field})
            ->countBy()
            ->sortDesc()
            ->all();
    }

    /**
     * @return array<string, int> Y-m-d => visits
     */
    public function visitsPerDay(): array
    {
        $days = [];

        for ($day = $this->from->copy(); $day->lte($this->to) && count($days) < 400; $day->addDay()) {
            $days[$day->toDateString()] = 0;
        }

        foreach ($this->visits as $visit) {
            $key = $visit->visited_at->toDateString();
            $days[$key] = ($days[$key] ?? 0) + 1;
        }

        return $days;
    }

    /**
     * Products most often found missing, with how many stores were affected.
     *
     * @return Collection<int, array{name: string, line: ?string, count: int, stores: int}>
     */
    public function topOutOfStock(int $limit = 15): Collection
    {
        return $this->items
            ->whereIn('stock_status', self::OUT_STATUSES)
            ->groupBy(fn (VisitItem $i) => $i->product_id ? 'p'.$i->product_id : 'n'.mb_strtolower((string) $i->product_name))
            ->map(fn (Collection $group) => [
                'name' => $group->first()->displayName(),
                'line' => $group->first()->product?->line,
                'count' => $group->count(),
                'stores' => $group->map(fn ($i) => $i->visit->store_id)->unique()->count(),
            ])
            ->sortByDesc('count')
            ->take($limit)
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function byMerchandiser(): Collection
    {
        return $this->visits->groupBy('user_id')->map(function (Collection $visits) {
            $items = $this->items->whereIn('visit_id', $visits->pluck('id'));

            return [
                'name' => $visits->first()->user?->name,
                'visits' => $visits->count(),
                'stores' => $visits->pluck('store_id')->unique()->count(),
                'products' => $items->count(),
                'out_of_stock' => $items->whereIn('stock_status', self::OUT_STATUSES)->count(),
                'photos' => $visits->sum(fn ($v) => $v->media->count()),
            ];
        })->sortByDesc('visits')->values();
    }

    /**
     * Items of one visit that count for the current division filter.
     *
     * @return Collection<int, VisitItem>
     */
    public function itemsOf(Visit $visit): Collection
    {
        return $this->items->where('visit_id', $visit->id)->values();
    }

    public function periodLabel(): string
    {
        return $this->from->isSameDay($this->to)
            ? $this->from->format('l, j F Y')
            : $this->from->format('j M Y').' – '.$this->to->format('j M Y');
    }
}
