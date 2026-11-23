<?php

namespace Esoboy\Ui\Support;

use InvalidArgumentException;

final class Icons
{
    /** @return list<string> */
    public static function names(): array
    {
        return array_keys(self::catalog());
    }

    public static function size(string|int|float $size): int|float
    {
        $pixels = match ($size) {
            'sm' => 14,
            'md' => 16,
            'lg' => 20,
            default => $size,
        };

        if (! is_numeric($pixels) || ! is_finite((float) $pixels) || $pixels <= 0) {
            throw new InvalidArgumentException(__('ui::errors.icon_size'));
        }

        return $pixels + 0;
    }

    public static function content(string $name): string
    {
        if (! preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $name)) {
            throw new InvalidArgumentException(__('ui::errors.icon_name'));
        }

        return self::catalog()[$name] ?? throw new InvalidArgumentException(__('ui::errors.unknown_icon', ['name' => $name]));
    }

    /** @return array<string, string> */
    private static function catalog(): array
    {
        static $catalog;

        return $catalog ??= require __DIR__.'/../../resources/icons/catalog.php';
    }
}
