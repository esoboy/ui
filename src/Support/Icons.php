<?php

namespace Esoboy\Ui\Support;

use InvalidArgumentException;

final class Icons
{
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

        static $catalog;
        $catalog ??= require __DIR__.'/../../resources/icons/catalog.php';

        return $catalog[$name] ?? throw new InvalidArgumentException(__('ui::errors.unknown_icon', ['name' => $name]));
    }
}
