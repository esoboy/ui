<?php

namespace Esoboy\Ui\Support;

use InvalidArgumentException;

final class StatisticValue
{
    public static function format(string|int|float|null $value, ?int $precision, string $groupSeparator, string $decimalSeparator): string
    {
        if ($precision !== null && $precision < 0) {
            throw new InvalidArgumentException(__('ui::errors.statistic_precision'));
        }
        if ($value === null || $value === '') {
            return '—';
        }

        $text = is_float($value) && is_finite($value) ? self::plainDecimal($value) : (string) $value;
        if (! preg_match('/\A(-?)(\d*)(?:\.(\d+))?\z/', $text, $parts) || $text === '-') {
            return $text;
        }

        $integer = $parts[2] === '' ? '0' : $parts[2];
        $integer = preg_replace_callback('/\B(?=(\d{3})+\z)/', fn () => $groupSeparator, $integer);
        $fraction = $parts[3] ?? '';
        if ($precision !== null) {
            $fraction = str_pad(substr($fraction, 0, $precision), $precision, '0');
        }

        return $parts[1].$integer.($fraction === '' ? '' : $decimalSeparator.$fraction);
    }

    private static function plainDecimal(float $value): string
    {
        $parts = explode('e', json_encode(abs($value), JSON_THROW_ON_ERROR));
        $mantissa = $parts[0];
        $sign = $value < 0 ? '-' : '';
        if (! isset($parts[1])) {
            return $sign.$mantissa;
        }

        // JSON supplies the shortest round-trippable digits. Move the decimal point
        // before applying display precision so very small values keep their digits.
        $digits = rtrim(str_replace('.', '', $mantissa), '0');
        $point = 1 + (int) $parts[1];
        if ($point <= 0) {
            return $sign.'0.'.str_repeat('0', -$point).$digits;
        }

        $digits = str_pad($digits, $point, '0');

        return $sign.rtrim(substr_replace($digits, '.', $point, 0), '.');
    }
}
