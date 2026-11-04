<?php

namespace Esoboy\Ui\Support;

use DateTimeInterface;
use Illuminate\Support\Facades\Date;

/**
 * Countdown's first render: the time left, formatted as its script formats it
 * (resources/js/support/countdown.js), so the page shows the right time before any script runs.
 */
final class Countdown
{
    private const UNITS = ['D' => 86400, 'H' => 3600, 'm' => 60, 's' => 1];

    /**
     * Milliseconds left until $until, or $seconds' worth when there is no $until.
     */
    public static function remaining(DateTimeInterface|string|null $until, ?int $seconds): int
    {
        if ($until === null) {
            return max(0, ($seconds ?? 0) * 1000);
        }

        return max(0, (int) round(Date::now()->diffInMilliseconds(Date::parse($until))));
    }

    /**
     * D, H, m and s, padded to their length (HH: 05); the largest one in the format takes
     * all above it (HH:mm with no D counts hours past 24). [text] is literal.
     */
    public static function format(int $milliseconds, string $format): string
    {
        preg_match_all('/\[[^\]]*\]|D+|H+|m+|s+|[^DHms]/u', $format, $matches);

        $tokens = $matches[0];
        $rest = (int) ceil(max(0, $milliseconds) / 1000);
        $values = [];

        foreach (self::UNITS as $unit => $size) {
            if (array_filter($tokens, fn (string $token) => $token[0] === $unit)) {
                $values[$unit] = intdiv($rest, $size);
                $rest -= $values[$unit] * $size;
            }
        }

        return implode('', array_map(fn (string $token) => match (true) {
            $token[0] === '[' => substr($token, 1, -1),
            isset($values[$token[0]]) => str_pad((string) $values[$token[0]], strlen($token), '0', STR_PAD_LEFT),
            default => $token,
        }, $tokens));
    }
}
