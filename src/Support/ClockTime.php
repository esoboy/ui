<?php

namespace Esoboy\Ui\Support;

use InvalidArgumentException;

final class ClockTime
{
    public static function read(mixed $value, bool $seconds): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $pattern = $seconds ? '/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/D' : '/^(?:[01]\d|2[0-3]):[0-5]\d$/D';
        if (! is_string($value) || ! preg_match($pattern, $value)) {
            throw new InvalidArgumentException(__('ui::errors.time_format', ['format' => $seconds ? 'HH:mm:ss' : 'HH:mm']));
        }

        return $value;
    }

    public static function replace(?string $time, bool $seconds, string $unit, int $number): ?string
    {
        $index = array_search($unit, $seconds ? ['hour', 'minute', 'second'] : ['hour', 'minute'], true);
        if ($index === false || $number < 0 || $number > ($unit === 'hour' ? 23 : 59)) {
            return null;
        }
        $parts = explode(':', $time ?? self::midnight($seconds));
        $parts[$index] = sprintf('%02d', $number);

        return implode(':', $parts);
    }

    public static function midnight(bool $seconds): string
    {
        return $seconds ? '00:00:00' : '00:00';
    }
}
