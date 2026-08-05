<?php

namespace App\Services;

use App\Models\Settings\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    protected const CACHE_KEY = 'settings.all';

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()->get($key, $default);
    }

    public function set(string $key, mixed $value, string $type = 'string', ?string $group = null): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type, 'group' => $group]
        );

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return Collection<string, mixed>
     */
    public function all(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            return Setting::all()->mapWithKeys(
                fn (Setting $setting) => [$setting->key => $this->cast($setting)]
            );
        });
    }

    protected function cast(Setting $setting): mixed
    {
        return match ($setting->type) {
            'integer' => (int) $setting->value,
            'float' => (float) $setting->value,
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode((string) $setting->value, true),
            default => $setting->value,
        };
    }
}
