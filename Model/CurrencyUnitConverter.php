<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model;

/**
 * Helper functions to convert between major and minor currency units while avoiding penny/cent differences
 * due to floating-point maths errors
 */
class CurrencyUnitConverter
{
    public static function majorToMinor(string $major): int
    {
        return (int) round($major * 100);
    }

    public static function minorToMajor(int $minor): float
    {
        return round($minor / 100);
    }
}
