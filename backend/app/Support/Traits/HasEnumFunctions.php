<?php

namespace App\Support\Traits;

trait HasEnumFunctions
{
    /**
     * Get the enum values as an array.
     *
     * @return array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
