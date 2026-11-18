<?php

namespace App\Services\Merchandising;

use App\Enums\MerchandisingDivision;
use App\Models\Products\Product;
use App\Models\Settings\LookupValue;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Support\Carbon;

/**
 * The choices the visit form offers. Issues, actions and store feedback are
 * lookup values admins can edit; stock, shelf and price-tag answers are fixed
 * because the reports are built on them.
 */
class MerchandisingOptions
{
    public function __construct(private readonly SettingsService $settings) {}

    /**
     * @return array<string, string> code => label in $locale
     */
    public function lookup(string $type, ?string $locale = null, bool $activeOnly = true): array
    {
        $locale ??= app()->getLocale();

        return LookupValue::whereHas('lookupType', fn ($q) => $q->where('code', $type))
            ->when($activeOnly, fn ($q) => $q->where('is_active', true))
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(fn (LookupValue $v) => [$v->code => $v->label[$locale] ?? $v->label['en'] ?? $v->code])
            ->all();
    }

    /**
     * Divisions this merchandiser covers; nobody assigned means both.
     *
     * @return list<string>
     */
    public function divisionsFor(User $user): array
    {
        $divisions = array_values(array_intersect((array) $user->merchandising_divisions, MerchandisingDivision::values()));

        return $divisions ?: MerchandisingDivision::values();
    }

    /**
     * The catalog the form searches in the browser, limited to the user's
     * divisions.
     *
     * @return list<array<string, mixed>>
     */
    public function catalogFor(User $user): array
    {
        return Product::query()
            ->with('category:id,division')
            ->where('is_active', true)
            ->whereHas('category', fn ($q) => $q->whereIn('division', $this->divisionsFor($user)))
            ->orderBy('line')->orderBy('title')
            ->get(['id', 'sku', 'title', 'line', 'image_path', 'category_id'])
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'sku' => $p->sku,
                'name' => $p->title,
                'line' => $p->line ?: '—',
                'division' => $p->category->division,
                'image' => $p->image_url,
            ])
            ->all();
    }

    public function now(): Carbon
    {
        $timezone = (string) $this->settings->get('timezone', config('app.timezone'));

        return now(in_array($timezone, timezone_identifiers_list(), true) ? $timezone : config('app.timezone'));
    }
}
