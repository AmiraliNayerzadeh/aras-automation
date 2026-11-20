<?php

namespace App\Services\Merchandising;

use App\Models\Merchandising\Store;

/**
 * Checks that a visit is recorded at the store. Inside the radius the visit is
 * "verified"; outside it the visit is refused. When the phone gives no
 * location, or the store has no coordinates yet, the visit is still accepted
 * but flagged, so admins can see which visits weren't confirmed on site.
 */
class VisitLocation
{
    public const RADIUS_M = 100;

    /**
     * Phones indoors often report 20-60 m accuracy; part of that uncertainty
     * is forgiven so someone standing in the store isn't turned away, but no
     * more than this, so a vague fix can't stretch the radius far.
     */
    public const MAX_ACCURACY_ALLOWANCE_M = 50;

    public const VERIFIED = 'verified';

    public const NO_LOCATION = 'no_location';

    public const STORE_UNMAPPED = 'store_unmapped';

    public const STATUSES = [self::VERIFIED, self::NO_LOCATION, self::STORE_UNMAPPED];

    /**
     * @param  array{lat: float, lng: float, accuracy?: ?float}|null  $location
     * @return array{status: string, distance: ?int, allowed: bool}
     */
    public function assess(?array $location, Store $store): array
    {
        if (! $location) {
            return ['status' => self::NO_LOCATION, 'distance' => null, 'allowed' => true];
        }

        if ($store->latitude === null || $store->longitude === null) {
            return ['status' => self::STORE_UNMAPPED, 'distance' => null, 'allowed' => true];
        }

        $distance = (int) round(self::distance((float) $location['lat'], (float) $location['lng'], (float) $store->latitude, (float) $store->longitude));
        $allowance = min((float) ($location['accuracy'] ?? 0), self::MAX_ACCURACY_ALLOWANCE_M);

        return [
            'status' => self::VERIFIED,
            'distance' => $distance,
            'allowed' => $distance <= self::RADIUS_M + $allowance,
        ];
    }

    /**
     * Great-circle distance in metres.
     */
    public static function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $rad = M_PI / 180;
        $a = sin(($lat2 - $lat1) * $rad / 2) ** 2
            + cos($lat1 * $rad) * cos($lat2 * $rad) * sin(($lng2 - $lng1) * $rad / 2) ** 2;

        return 6371000 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
