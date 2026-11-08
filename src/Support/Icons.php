<?php

namespace Esoboy\Ui\Support;

use InvalidArgumentException;

/**
 * The Lucide icons the package ships (resources/icons/lucide.php). Only those exist;
 * a newer Lucide comes with a newer package.
 */
final class Icons
{
    /** @var array{version: string, icons: array<string, string>}|null */
    private static ?array $lucide = null;

    /**
     * The inner markup of an icon.
     */
    public static function markup(string $name): string
    {
        $lucide = self::$lucide ??= require __DIR__.'/../../resources/icons/lucide.php';

        if (isset($lucide['icons'][$name])) {
            return $lucide['icons'][$name];
        }

        throw new InvalidArgumentException("Lucide {$lucide['version']} has no icon [{$name}].");
    }
}
