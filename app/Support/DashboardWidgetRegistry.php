<?php

namespace App\Support;

use Closure;

/**
 * Extension point for the "My Actions" dashboard area. Future modules bind
 * their own action-item resolvers here (during their service provider's
 * boot()) instead of the dashboard hardcoding a widget list per module.
 */
class DashboardWidgetRegistry
{
    /** @var array<string, Closure> */
    protected array $resolvers = [];

    public function register(string $key, Closure $resolver): void
    {
        $this->resolvers[$key] = $resolver;
    }

    /**
     * @return array<int, array{label: string, url: string, count: int}>
     */
    public function resolve(): array
    {
        $items = [];

        foreach ($this->resolvers as $resolver) {
            foreach ((array) $resolver() as $item) {
                $items[] = $item;
            }
        }

        return $items;
    }
}
