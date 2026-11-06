<?php

namespace Esoboy\Ui\Support;

final class TimeOptions
{
    private const UNITS = ['hour' => [3600, 24], 'minute' => [60, 60], 'second' => [1, 60]];

    private int $step;

    private int $min;

    private int $max;

    public readonly bool $withSeconds;

    public function __construct(mixed $step = null, mixed $min = null, mixed $max = null, public readonly bool $combined = false)
    {
        $interval = $step === 'any' ? 1 : filter_var($step, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $this->step = $combined
            ? ($interval && $interval % 60 === 0 ? $interval : 300)
            : ($interval ?: 60);
        $this->min = self::parse($min) ?? 0;
        $this->max = self::parse($max) ?? 86399;
        $this->withSeconds = $this->step % 60 !== 0 || $this->min % 60 !== 0;
    }

    public static function parse(mixed $time): ?int
    {
        if (! is_string($time) || ! preg_match('/\A([01]\d|2[0-3]):([0-5]\d)(?::([0-5]\d))?\z/', $time, $parts)) {
            return null;
        }

        return (int) $parts[1] * 3600 + (int) $parts[2] * 60 + (int) ($parts[3] ?? 0);
    }

    public function initial(mixed $time): string
    {
        return $this->format($this->current($time));
    }

    /** @return array<string, array{current: int, options: array<int, string>}> */
    public function columns(mixed $time): array
    {
        $current = $this->current($time);
        $columns = [];
        foreach (self::UNITS as $unit => [$size, $count]) {
            if ($unit === 'second' && ! $this->withSeconds) {
                break;
            }
            $selected = intdiv($current, $size) % $count;
            $options = [];
            for ($option = 0; $option < $count; $option++) {
                if ($this->candidate($current, $size, $count, $option) !== null) {
                    $options[$option] = sprintf('%02d', $option);
                }
            }
            $columns[$unit] = ['current' => $selected, 'options' => $options];
        }

        return $columns;
    }

    public function choose(mixed $time, string $unit, int $option): ?string
    {
        if ($this->combined) {
            return $unit === 'combined' && $option >= 0 && $option <= 86399 && $this->first($option, $option) === $option
                ? $this->format($option)
                : null;
        }
        if (! isset(self::UNITS[$unit]) || ($unit === 'second' && ! $this->withSeconds)) {
            return null;
        }
        [$size, $count] = self::UNITS[$unit];
        if ($option < 0 || $option >= $count) {
            return null;
        }
        $candidate = $this->candidate($this->current($time), $size, $count, $option);

        return $candidate === null ? null : $this->format($candidate);
    }

    /** @return array{hour: int, hours: list<int>, previous: ?int, next: ?int, options: array<int, string>, current: int} */
    public function slots(mixed $time, ?int $hour): array
    {
        $hours = [];
        for ($index = 0; $index < 24; $index++) {
            if ($this->first($index * 3600, $index * 3600 + 3599) !== null) {
                $hours[] = $index;
            }
        }

        $current = $this->current($time);
        $hour = in_array($hour, $hours, true) ? $hour : intdiv($current, 3600);
        $index = array_search($hour, $hours, true);
        $previous = $index === false ? null : ($hours[$index - 1] ?? null);
        $next = $index === false ? null : ($hours[$index + 1] ?? null);
        $end = $hour * 3600 + 3599;
        $options = [];
        for ($slot = $this->first($hour * 3600, $end); $slot !== null;) {
            $options[$slot] = $this->format($slot);
            $slot = $this->step > $end - $slot ? null : $this->first($slot + $this->step, $end);
        }

        return compact('hour', 'hours', 'previous', 'next', 'options', 'current');
    }

    private function current(mixed $time): int
    {
        $seconds = self::parse($time);
        if ($seconds === null) {
            return $this->min;
        }
        if (! $this->withSeconds) {
            $seconds -= $seconds % 60;
        }

        return $this->first($seconds, 86399) ?? $this->first(0, $seconds) ?? $this->min;
    }

    private function candidate(int $current, int $size, int $count, int $option): ?int
    {
        $start = $current - $current % ($size * $count) + $option * $size;
        $preferred = $start + $current % $size;

        return $this->first($preferred, $preferred) ?? $this->first($start, $start + $size - 1);
    }

    private function first(int $start, int $end): ?int
    {
        $windows = $this->min <= $this->max ? [[$this->min, $this->max]] : [[0, $this->max], [$this->min, 86399]];
        foreach ($windows as [$min, $max]) {
            $startInWindow = max($start, $min);
            $offset = ($startInWindow - $this->min) % $this->step;
            $adjustment = $offset > 0 ? $this->step - $offset : -$offset;
            if ($adjustment <= min($end, $max) - $startInWindow) {
                return $startInWindow + $adjustment;
            }
        }

        return null;
    }

    private function format(int $seconds): string
    {
        return gmdate($this->withSeconds ? 'H:i:s' : 'H:i', $seconds);
    }
}
