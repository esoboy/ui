<?php

namespace Esoboy\Ui\Livewire;

use Carbon\CarbonImmutable;
use Esoboy\Ui\Support\TimeOptions;
use Illuminate\Contracts\View\View;
use IntlDateFormatter;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Calendar extends Component
{
    use SyncsModel;

    #[Modelable]
    public mixed $value = null;

    /** @var array<string, mixed> */
    #[Reactive]
    public array $settings = [];

    public string $month = '';

    /** @var array{from: string, to: string} */
    #[Locked]
    public array $times = ['from' => '00:00', 'to' => '00:00'];

    /**
     * @param  array<string, mixed>  $settings
     */
    public function mount(array $settings = [], mixed $value = null): void
    {
        $this->settings = $settings;
        $this->value = $value;
        $first = is_array($value) ? ($value['from'] ?? $value[0] ?? null) : $value;
        $month = $settings['month'] ?? (is_string($first) ? $this->selectedMonth($first) : now()->format('Y-m'));
        $this->month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) ? $month : now()->format('Y-m');
        [$minimum, $maximum] = $this->monthBounds();
        if ($minimum && $this->month < $minimum) {
            $this->month = $minimum;
        }
        if ($maximum && $this->month > $maximum) {
            $this->month = $maximum;
        }
        $this->syncTimes();
    }

    public function goTo(string $month): void
    {
        if (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month) && $this->canShow($month)) {
            $this->month = $month;
        }
    }

    protected function modelChanged(): void
    {
        $first = is_array($this->value) ? ($this->value['from'] ?? $this->value[0] ?? null) : $this->value;
        if (is_string($first)) {
            $this->goTo($this->selectedMonth($first));
        }
        $this->syncTimes();
    }

    private function syncTimes(): void
    {
        if (! ($this->settings['time'] ?? false)) {
            return;
        }

        $options = $this->timeOptions();
        $value = is_array($this->value) ? $this->value : ['from' => $this->value];
        foreach (['from' => $value['from'] ?? $value[0] ?? null, 'to' => $value['to'] ?? null] as $part => $at) {
            $time = is_string($at) ? substr($at, 11) : '';
            $this->times[$part] = TimeOptions::parse($time) === null
                ? $options->initial($this->settings['timeValue'] ?? null)
                : $time;
        }
    }

    protected function timeOptions(): TimeOptions
    {
        return new TimeOptions($this->settings['step'] ?? null, combined: ($this->settings['timeMode'] ?? null) === 'combined');
    }

    public function previous(): void
    {
        $this->goTo(CarbonImmutable::parse($this->month.'-01')->subMonth()->format('Y-m'));
    }

    public function next(): void
    {
        $this->goTo(CarbonImmutable::parse($this->month.'-01')->addMonth()->format('Y-m'));
    }

    public function select(string $date): void
    {
        if (! $this->selectable($date)) {
            return;
        }

        $mode = $this->settings['mode'] ?? 'single';
        if ($mode === 'range') {
            $from = $this->selectionKey($this->value['from'] ?? '') ?: null;
            $to = $this->selectionKey($this->value['to'] ?? '') ?: null;
            $range = ! $from || $to ? ['from' => $date, 'to' => null]
                : ($date < $from ? ['from' => $date, 'to' => $from] : ['from' => $from, 'to' => $date]);
            $this->value = ['from' => $this->at($range['from'], 'from'), 'to' => $this->at($range['to'], 'to')];
        } elseif ($mode === 'multiple') {
            $values = (array) $this->value;
            $dates = array_map($this->selectionKey(...), $values);
            $this->value = in_array($date, $dates, true)
                ? array_values(array_filter($values, fn ($value) => $this->selectionKey($value) !== $date))
                : [...$values, $this->at($date, 'from')];
            sort($this->value);
        } else {
            $this->value = $this->at($date, 'from');
        }
        $this->dispatch('ui-calendar-picked', close: $mode === 'single' || ($mode === 'range' && ! empty($this->value['to'])));
    }

    public function preset(string $label): void
    {
        if ($this->settings['disabled'] ?? false) {
            return;
        }
        $value = $this->settings['presets'][$label] ?? null;
        if ($value === null) {
            return;
        }
        $dates = is_array($value) ? $value : [$value];
        foreach ($dates as $date) {
            if ($date && (! is_string($date) || ! $this->selectable($this->selectionKey($date)))) {
                return;
            }
        }
        if ($this->settings['time'] ?? false) {
            $value = match ($this->settings['mode'] ?? 'single') {
                'range' => ['from' => $this->at($value['from'] ?? null, 'from'), 'to' => $this->at($value['to'] ?? null, 'to')],
                'multiple' => array_map(fn ($date) => $this->at($date, 'from'), $value),
                default => $this->at($value, 'from'),
            };
        }
        $this->value = $value;
        $first = is_array($value) ? ($value['from'] ?? $value[0] ?? null) : $value;
        if ($first) {
            $this->goTo($this->selectedMonth($first));
        }
        $this->dispatch('ui-calendar-picked', close: true);
    }

    protected function at(?string $date, string $part): ?string
    {
        return $date && ($this->settings['time'] ?? false) ? $date.' '.$this->times[$part] : $date;
    }

    protected function selectionKey(?string $date): string
    {
        return substr($date ?? '', 0, 10);
    }

    protected function selectedMonth(string $date): string
    {
        return substr($date, 0, 7);
    }

    protected function selectable(string $date): bool
    {
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed && $parsed->format('Y-m-d') === $date && ! $this->disabled($date);
    }

    public function disabled(string $date): bool
    {
        return ($this->settings['disabled'] ?? false) || (! empty($this->settings['min']) && $date < substr($this->settings['min'], 0, 10))
            || (! empty($this->settings['max']) && $date > substr($this->settings['max'], 0, 10))
            || in_array($date, $this->settings['disabledDates'] ?? [], true);
    }

    public function canShow(string $month): bool
    {
        [$min, $max] = $this->monthBounds();

        return (! $min || $month >= $min) && (! $max || $month <= $max);
    }

    /** @return array{?string, ?string} */
    protected function monthBounds(): array
    {
        return [
            $this->settings['startMonth'] ?? (! empty($this->settings['min']) ? substr($this->settings['min'], 0, 7) : null),
            $this->settings['endMonth'] ?? (! empty($this->settings['max']) ? substr($this->settings['max'], 0, 7) : null),
        ];
    }

    public function format(string $date, string $pattern): string
    {
        $generator = \IntlDatePatternGenerator::create(app()->getLocale());
        $pattern = $generator?->getBestPattern(preg_replace('/[^a-zA-Z]/', '', $pattern) ?? $pattern) ?: $pattern;
        $formatter = new IntlDateFormatter(app()->getLocale(), IntlDateFormatter::NONE, IntlDateFormatter::NONE, config('app.timezone'), pattern: $pattern);

        return $formatter->format(CarbonImmutable::parse($date)) ?: $date;
    }

    /** @return list<array<string, mixed>> */
    public function months(): array
    {
        $first = CarbonImmutable::parse($this->month.'-01');
        $start = $this->settings['weekStartsOn'] ?? (int) \IntlCalendar::createInstance(null, app()->getLocale())->getFirstDayOfWeek() - 1;
        $value = $this->value;
        $mode = $this->settings['mode'] ?? 'single';
        $from = $mode === 'range' ? substr($value['from'] ?? '', 0, 10) : null;
        $to = $mode === 'range' ? substr($value['to'] ?? $value['from'] ?? '', 0, 10) : null;
        $selected = $mode === 'range' ? [] : array_map(fn ($at) => substr($at ?? '', 0, 10), (array) $value);
        $focus = $from ?: ($selected[0] ?? null);
        $focus ??= now()->format('Y-m') === $this->month ? now()->format('Y-m-d') : $this->month.'-01';
        $months = [];

        for ($offset = 0; $offset < max(1, min(12, (int) ($this->settings['numberOfMonths'] ?? 1))); $offset++) {
            $month = $first->addMonths($offset);
            $day = $month->subDays(($month->dayOfWeek - $start + 7) % 7);
            $weeks = [];
            for ($week = 0; $week < 6; $week++) {
                if ($week > 0 && $day->month !== $month->month) {
                    break;
                }
                $days = [];
                for ($index = 0; $index < 7; $index++, $day = $day->addDay()) {
                    $key = $day->format('Y-m-d');
                    $middle = $from && $to && $key > $from && $key < $to;
                    $chosen = $mode === 'range' ? ($key === $from || $key === $to || $middle) : in_array($key, $selected, true);
                    $flags = array_filter([
                        'data-today' => $day->isToday(), 'data-outside' => $day->month !== $month->month,
                        'data-selected' => $chosen, 'data-selected-single' => $mode !== 'range' && $chosen,
                        'data-range-start' => $key === $from, 'data-range-end' => $key === $to,
                        'data-range-middle' => (bool) $middle, 'data-disabled' => $this->disabled($key),
                    ]);
                    $days[] = ['key' => $key, 'number' => $day->day, 'label' => $this->format($key, 'EEEE, MMMM d, y'), 'flags' => array_map(fn () => 'true', $flags), 'selected' => $chosen, 'disabled' => $this->disabled($key), 'focus' => $key === $focus, 'hidden' => $day->month !== $month->month && ! ($this->settings['showOutsideDays'] ?? true)];
                }
                $weeks[] = $days;
            }
            $weekdays = [];
            for ($index = 0; $index < 7; $index++) {
                $date = CarbonImmutable::parse('2023-01-01')->addDays(($start + $index) % 7)->format('Y-m-d');
                $short = $this->format($date, 'EEE');
                $weekdays[] = ['label' => $this->format($date, 'EEEE'), 'text' => preg_match('/^[a-z]/i', $short) ? mb_substr($short, 0, 2) : $short];
            }
            $months[] = ['key' => $month->format('Y-m'), 'year' => $month->year, 'month' => $month->month, 'caption' => $this->format($month->format('Y-m-d'), 'MMMM y'), 'weeks' => $weeks, 'weekdays' => $weekdays];
        }

        return $months;
    }

    public function render(): View
    {
        return view('ui::livewire.calendar', ['months' => $this->months()]);
    }
}
