<?php

namespace Esoboy\Ui\Livewire;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use IntlDateFormatter;
use Livewire\Attributes\Locked;

class DatePicker extends Calendar
{
    /** @var array{from: ?int, to: ?int} */
    #[Locked]
    public array $shownHours = ['from' => null, 'to' => null];

    private function granularity(): string
    {
        return $this->settings['granularity'] ?? 'day';
    }

    /** @return array{string, string}|null */
    private function periodMonths(string $value): ?array
    {
        if ($this->granularity() === 'month' && preg_match('/^(?!0000)\d{4}-(0[1-9]|1[0-2])$/D', $value)) {
            return [$value, $value];
        }
        if ($this->granularity() === 'quarter' && preg_match('/^((?!0000)\d{4})-Q([1-4])$/D', $value, $matches)) {
            $last = (int) $matches[2] * 3;

            return [sprintf('%s-%02d', $matches[1], $last - 2), sprintf('%s-%02d', $matches[1], $last)];
        }

        return null;
    }

    protected function selectionKey(?string $date): string
    {
        return $this->granularity() === 'day' ? parent::selectionKey($date) : ($date ?? '');
    }

    protected function selectedMonth(string $date): string
    {
        $period = $this->periodMonths($date);
        if ($period === null) {
            return parent::selectedMonth($date);
        }

        [$min] = $this->monthBounds();

        return max($period[0], $min ?? $period[0]);
    }

    /** @return array{?string, ?string} */
    protected function monthBounds(): array
    {
        [$min, $max] = parent::monthBounds();

        return [$this->periodMonths($min ?? '')[0] ?? $min, $this->periodMonths($max ?? '')[1] ?? $max];
    }

    protected function selectable(string $date): bool
    {
        if ($this->granularity() === 'day') {
            return parent::selectable($date);
        }

        $period = $this->periodMonths($date);
        [$min, $max] = $this->monthBounds();

        return $period !== null && ! ($this->settings['disabled'] ?? false)
            && (! $min || $period[1] >= $min) && (! $max || $period[0] <= $max)
            && ! in_array($date, $this->settings['disabledDates'] ?? [], true);
    }

    private function monthInYear(int $year): ?string
    {
        if ($year < 1 || $year > 9999) {
            return null;
        }

        [$min, $max] = $this->monthBounds();
        $first = sprintf('%04d-01', $year);
        $last = sprintf('%04d-12', $year);

        return ($min && $last < $min) || ($max && $first > $max) ? null : max($first, $min ?? $first);
    }

    public function goToYear(int $year): void
    {
        if ($this->granularity() !== 'day' && ($month = $this->monthInYear($year)) !== null) {
            $this->goTo($month);
        }
    }

    /** @return array<string, mixed> */
    private function yearPeriods(): array
    {
        $quarter = $this->granularity() === 'quarter';
        $year = (int) substr($this->month, 0, 4);
        $mode = $this->settings['mode'] ?? 'single';
        $from = $mode === 'range' ? ($this->value['from'] ?? null) : null;
        $to = $mode === 'range' ? ($this->value['to'] ?? null) : null;
        $options = [];
        foreach (range(1, $quarter ? 4 : 12) as $number) {
            $key = sprintf($quarter ? '%04d-Q%d' : '%04d-%02d', $year, $number);
            $date = sprintf('%04d-%02d-01', $year, $quarter ? $number * 3 - 2 : $number);
            $middle = $from && $to && $key > $from && $key < $to;
            $options[] = [
                'key' => $key,
                'text' => $this->format($date, $quarter ? 'QQQ' : 'MMM'),
                'label' => $this->format($date, $quarter ? 'yQQQQ' : 'yMMMM'),
                'pressed' => $mode === 'range' ? $key === $from || $key === $to || $middle : in_array($key, (array) $this->value, true),
                'middle' => (bool) $middle,
                'disabled' => ! $this->selectable($key),
            ];
        }

        [$min, $max] = $this->monthBounds();

        return [
            'year' => $year,
            'startYear' => $min ? (int) substr($min, 0, 4) : max(1, $year - 100),
            'endYear' => $max ? (int) substr($max, 0, 4) : min(9999, $year + 10),
            'previous' => $this->monthInYear($year - 1) !== null,
            'next' => $this->monthInYear($year + 1) !== null,
            'options' => $options,
        ];
    }

    /** @return 'from'|'to'|null */
    private function timePart(string $part): ?string
    {
        if (($this->settings['disabled'] ?? false) || ! ($this->settings['time'] ?? false)) {
            return null;
        }

        return match ($part) {
            'from' => 'from',
            'to' => ($this->settings['mode'] ?? 'single') === 'range' ? 'to' : null,
            default => null,
        };
    }

    public function showTimeHour(string $part, int $hour): void
    {
        $part = $this->timePart($part);
        if ($part !== null && $hour >= 0 && $hour < 24) {
            $this->shownHours[$part] = $hour;
        }
    }

    protected function modelChanged(): void
    {
        parent::modelChanged();
        $this->shownHours = ['from' => null, 'to' => null];
    }

    public function pickTime(string $part, string $unit, int $option): void
    {
        $part = $this->timePart($part);
        if ($part === null) {
            return;
        }

        $time = $this->timeOptions()->choose($this->times[$part], $unit, $option);
        if ($time === null) {
            return;
        }

        $this->times[$part] = $time;
        $this->shownHours[$part] = null;
        $mode = $this->settings['mode'] ?? 'single';
        if ($mode === 'range') {
            if (isset($this->value[$part])) {
                $this->value[$part] = $this->at(substr($this->value[$part], 0, 10), $part);
            }
        } elseif ($mode === 'multiple') {
            $this->value = array_map(fn ($date) => $this->at(substr($date, 0, 10), $part), (array) $this->value);
        } elseif ($this->value) {
            $this->value = $this->at(substr($this->value, 0, 10), $part);
        }
    }

    public function label(): string
    {
        $mode = $this->settings['mode'] ?? 'single';
        $format = function (?string $value) use ($mode): string {
            if (! $value) {
                return '';
            }
            if ($this->granularity() !== 'day') {
                $period = $this->periodMonths($value);

                return $period ? $this->format($period[0].'-01', $this->granularity() === 'quarter' ? 'yQQQ' : 'yMMMM') : '';
            }
            if (! ($this->settings['time'] ?? false)) {
                return $this->format($value, $mode === 'single' ? 'yMMMMd' : 'yMMMdd');
            }
            $formatter = new IntlDateFormatter(app()->getLocale(), $mode === 'single' ? IntlDateFormatter::LONG : IntlDateFormatter::MEDIUM, $this->timeOptions()->withSeconds ? IntlDateFormatter::MEDIUM : IntlDateFormatter::SHORT, config('app.timezone'));

            return str_replace("\u{202F}", ' ', $formatter->format(CarbonImmutable::parse($value)) ?: $value);
        };

        return is_array($this->value)
            ? implode($mode === 'range' ? ' - ' : ', ', array_map($format, array_values(array_filter($this->value))))
            : $format($this->value);
    }

    public function render(): View
    {
        $daily = $this->granularity() === 'day';

        return view('ui::livewire.date-picker', [
            'months' => $daily ? $this->months() : [],
            'periods' => $daily ? null : $this->yearPeriods(),
            'label' => $this->label(),
            'timeOptions' => $this->timeOptions(),
        ]);
    }
}
