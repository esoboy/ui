<?php

namespace Esoboy\Ui\Support;

/**
 * Numbers parts in render order. Blade renders a component's children before the
 * component itself, so children count up and the parent resets the count after them
 * (Steps uses it to know each step's position).
 */
final class Sequence
{
    /** @var array<string, int> */
    private array $counts = [];

    public static function next(string $name): int
    {
        $sequence = app(self::class);

        return $sequence->counts[$name] = ($sequence->counts[$name] ?? 0) + 1;
    }

    public static function reset(string $name): int
    {
        $sequence = app(self::class);
        $count = $sequence->counts[$name] ?? 0;

        unset($sequence->counts[$name]);

        return $count;
    }
}
