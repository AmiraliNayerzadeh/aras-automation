<?php

namespace App\Enums;

/**
 * Aras Food's two product divisions: food under the TOP SUN name, household
 * and personal care under UNILEVER.
 */
enum MerchandisingDivision: string
{
    case TopSun = 'topsun';
    case Unilever = 'unilever';

    public function label(): string
    {
        return match ($this) {
            self::TopSun => 'TOP SUN',
            self::Unilever => 'UNILEVER',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
