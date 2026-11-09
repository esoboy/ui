<?php

namespace Esoboy\Ui\Support;

use DateTimeInterface;

/**
 * Date Picker presets keyed by label, with a single value, a list or a range
 * (['from' => …, 'to' => …] or [from, to]).
 * Dates may be strings or DateTimeInterface.
 */
final class DatePresets
{
    /**
     * @param  array<string, mixed>  $presets
     * @return array<string, string|list<string|null>|array{from: string|null, to: string|null}|null>
     */
    public static function normalize(array $presets, string $mode, string $granularity = 'day'): array
    {
        return array_map(fn ($value) => match ($mode) {
            'range' => [
                'from' => self::key($value['from'] ?? $value[0] ?? null, $granularity),
                'to' => self::key($value['to'] ?? $value[1] ?? null, $granularity),
            ],
            'multiple' => array_values(array_map(fn ($date) => self::key($date, $granularity), (array) $value)),
            default => self::key($value, $granularity),
        }, $presets);
    }

    private static function key(mixed $date, string $granularity): ?string
    {
        if ($date instanceof DateTimeInterface) {
            return match ($granularity) {
                'month' => $date->format('Y-m'),
                'quarter' => $date->format('Y-\Q').(int) ceil((int) $date->format('n') / 3),
                default => $date->format('Y-m-d'),
            };
        }

        if ($date === null || $date === '') {
            return null;
        }

        return $granularity === 'day' ? substr((string) $date, 0, 10) : (string) $date;
    }
}
