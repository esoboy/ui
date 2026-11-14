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

    private function isMonth(string $value): bool
    {
        return preg_match('/^(?!0000)\d{4}-(0[1-9]|1[0-2])$/D', $value) === 1;
    }

    protected function selectionKey(?string $date): string
    {
        return $this->granularity() === 'month' ? ($date ?? '') : parent::selectionKey($date);
    }

    protected function selectedMonth(string $date): string
    {
        if ($this->granularity() === 'month' && $this->isMonth($date)) {
            [$min] = $this->monthBounds();

            return max($date, $min ?? $date);
        }

        return parent::selectedMonth($date);
    }

    protected function selectable(string $date): bool
    {
        if ($this->granularity() !== 'month') {
            return parent::selectable($date);
        }

        return $this->isMonth($date) && $this->canShow($date) && ! ($this->settings['disabled'] ?? false)
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
        if ($this->granularity() === 'month' && ($month = $this->monthInYear($year)) !== null) {
            $this->goTo($month);
        }
    }

    /** @return array<string, mixed> */
    private function yearPeriods(): array
    {
        $year = (int) substr($this->month, 0, 4);
        $mode = $this->settings['mode'] ?? 'single';
        $from = $mode === 'range' ? ($this->value['from'] ?? null) : null;
        $to = $mode === 'range' ? ($this->value['to'] ?? null) : null;
        $options = [];
        foreach (range(1, 12) as $number) {
            $key = sprintf('%04d-%02d', $year, $number);
            $date = $key.'-01';
            $middle = $from && $to && $key > $from && $key < $to;
            $options[] = [
                'key' => $key,
                'text' => $this->format($date, 'MMM'),
                'label' => $this->format($date, 'yMMMM'),
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
            if ($this->granularity() === 'month') {
                return $this->isMonth($value) ? $this->format($value.'-01', 'yMMMM') : '';
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
        $monthly = $this->granularity() === 'month';

        return view('ui::livewire.date-picker', [
            'months' => $monthly ? [] : $this->months(),
            'periods' => $monthly ? $this->yearPeriods() : null,
            'label' => $this->label(),
            'timeOptions' => $this->timeOptions(),
        ]);
    }
}
