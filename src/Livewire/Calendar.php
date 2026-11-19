<?php

namespace Esoboy\Ui\Livewire;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;

/**
 * @property-read list<string> $selected
 * @property-read CarbonImmutable $period
 * @property-read ?string $navigationStart
 * @property-read ?string $navigationEnd
 * @property-read ?string $lastPageMonth
 * @property-read list<array{date: string, label: int, current: bool, disabled: bool, hidden: bool, selected: bool, today: bool}> $days
 */
class Calendar extends ValueControl
{
    #[Reactive]
    public ?string $min = null;

    #[Reactive]
    public ?string $max = null;

    #[Reactive]
    public bool $multiple = false;

    #[Reactive]
    public bool $range = false;

    #[Reactive]
    public int $numberOfMonths = 1;

    #[Reactive]
    public string $captionLayout = 'label';

    #[Reactive]
    public string $monthLabelFormat = 'short';

    #[Reactive]
    public ?string $startMonth = null;

    #[Reactive]
    public ?string $endMonth = null;

    #[Reactive]
    public int $weekStartsOn = 0;

    #[Reactive]
    public bool $showWeekNumber = false;

    #[Reactive]
    public bool $fixedWeeks = false;

    #[Reactive]
    public ?string $locale = null;

    /** @var array<array-key, mixed> ISO date labels validated before rendering. */
    #[Reactive]
    public array $dayLabels = [];

    /** @var array<array-key, mixed> ISO date classes validated before rendering. */
    #[Reactive]
    public array $dayClasses = [];

    /** @var array<array-key, mixed> ISO dates validated before rendering. */
    #[Reactive]
    public array $disabledDates = [];

    public string $month = '';

    public function mount(mixed $initialValue = null, ?string $initialMonth = null): void
    {
        parent::mount($initialValue);
        if ($initialMonth !== null && ! $this->validDate($initialMonth)) {
            throw new InvalidArgumentException(__('ui::errors.calendar_initial_month'));
        }
        $this->month = ($initialMonth ?? $this->selected[0] ?? CarbonImmutable::today()->toDateString());
        $this->month = substr($this->month, 0, 7).'-01';
    }

    /** @return list<string> */
    #[Computed]
    public function selected(): array
    {
        $value = $this->current;
        if ($value === null || $value === '') {
            return [];
        }
        $selection = $this->multiple || $this->range ? $value : [$value];
        if (! is_array($selection) || ! array_is_list($selection)) {
            throw new InvalidArgumentException(__('ui::errors.date_list'));
        }
        foreach ($selection as $date) {
            if (! is_string($date) || ! $this->validDate($date)) {
                throw new InvalidArgumentException(__('ui::errors.date_format'));
            }
        }
        if ($this->range && (count($selection) > 2 || (count($selection) === 2 && $selection[0] > $selection[1]))) {
            throw new InvalidArgumentException(__('ui::errors.date_range'));
        }

        return $selection;
    }

    #[Computed]
    public function period(): CarbonImmutable
    {
        if (! $this->validDate($this->month)) {
            throw new InvalidArgumentException(__('ui::errors.calendar_display_month'));
        }

        $period = CarbonImmutable::parse($this->month)->startOfMonth();
        $locale = $this->locale ?? app()->getLocale();
        $period->locale($locale === 'cn' ? 'zh_CN' : $locale);

        return $period;
    }

    /** @return list<array{date: string, label: int, current: bool, disabled: bool, hidden: bool, selected: bool, today: bool}> */
    #[Computed]
    public function days(): array
    {
        return $this->monthDays(0);
    }

    #[Computed]
    public function navigationStart(): ?string
    {
        $date = $this->startMonth ?? $this->min;

        if ($date !== null) {
            return substr($date, 0, 7).'-01';
        }
        if ($this->captionLayout !== 'dropdown') {
            return null;
        }

        $default = CarbonImmutable::today()->subYears(100)->startOfYear()->toDateString();
        $end = $this->endMonth ?? $this->max ?? $default;

        return min($default, substr($end, 0, 4).'-01-01');
    }

    #[Computed]
    public function navigationEnd(): ?string
    {
        $date = $this->endMonth ?? $this->max;

        if ($date !== null) {
            return CarbonImmutable::parse($date)->endOfMonth()->toDateString();
        }
        if ($this->captionLayout !== 'dropdown') {
            return null;
        }

        $default = CarbonImmutable::today()->endOfYear()->toDateString();
        $start = $this->startMonth ?? $this->min ?? $default;

        return max($default, substr($start, 0, 4).'-12-31');
    }

    /** @return list<array{date: string, label: int, current: bool, disabled: bool, hidden: bool, selected: bool, today: bool, inRange: bool}> */
    public function monthDays(int $offset): array
    {
        $period = $this->period->addMonths($offset);
        $start = $period->startOfWeek($this->weekStartsOn);
        $end = $period->endOfMonth()->endOfWeek(($this->weekStartsOn + 6) % 7);
        $count = $this->fixedWeeks ? 42 : (int) $start->diffInDays($end->startOfDay()) + 1;
        $days = [];
        for ($offset = 0; $offset < $count; $offset++) {
            $day = $start->addDays($offset);
            $date = $day->toDateString();
            $days[] = ['date' => $date, 'label' => $day->day, 'current' => $day->month === $period->month, 'disabled' => ! $this->allowed($date), 'hidden' => ($this->navigationStart !== null && $date < $this->navigationStart) || ($this->navigationEnd !== null && $date > $this->navigationEnd), 'selected' => in_array($date, $this->selected, true), 'today' => $day->isToday(), 'inRange' => $this->range && count($this->selected) === 2 && $date > $this->selected[0] && $date < $this->selected[1]];
        }

        return $days;
    }

    public function moveMonth(int $direction): void
    {
        if (in_array($direction, [-1, 1], true)) {
            $this->showMonth($this->period->addMonths($direction)->format('Y-m'));
        }
    }

    public function choose(string $date): void
    {
        if (! $this->validDate($date) || ! $this->allowed($date)) {
            return;
        }
        $selected = $this->selected;
        if ($this->range) {
            $next = count($selected) === 1 ? [$selected[0], $date] : [$date];
            sort($next);
            if (count($next) === 2) {
                foreach ($this->disabledDates as $excluded) {
                    if ($excluded >= $next[0] && $excluded <= $next[1]) {
                        return;
                    }
                }
            }
            $this->changeValue($next);
            unset($this->selected, $this->days);

            return;
        }
        $this->changeValue($this->multiple
            ? (in_array($date, $selected, true) ? array_values(array_diff($selected, [$date])) : [...$selected, $date])
            : $date);
        unset($this->selected, $this->days);
    }

    protected function validDate(string $date): bool
    {
        if (! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/D', $date, $parts)) {
            return false;
        }

        return checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1]);
    }

    protected function allowed(string $date): bool
    {
        return ($this->min === null || $date >= $this->min) && ($this->max === null || $date <= $this->max) && ! in_array($date, $this->disabledDates, true);
    }

    public function showMonth(string $value): void
    {
        $month = $value.'-01';
        if (! $this->disabled && $this->validDate($month) && $this->boundedMonth($month) === $month) {
            $this->displayMonth($month);
        }
    }

    /** @return list<array{value: int, label: string}> */
    #[Computed]
    public function years(): array
    {
        $year = $this->period->year;
        $first = $this->navigationStart !== null ? (int) substr($this->navigationStart, 0, 4) : max(1, $year - 100);
        $last = $this->lastPageMonth !== null ? (int) substr($this->lastPageMonth, 0, 4) : min(9999, $year + 20);

        return array_map(fn (int $value): array => ['value' => $value, 'label' => (string) $value], range($first, $last));
    }

    /** @return list<array{value: int, label: string, disabled: bool}> */
    #[Computed]
    public function months(): array
    {
        return array_map(function (int $value): array {
            $period = $this->period->setMonth($value);
            $month = $period->toDateString();

            return [
                'value' => $value,
                'label' => $period->isoFormat('MMMM'),
                'disabled' => $this->boundedMonth($month) !== $month,
            ];
        }, range(1, 12));
    }

    public function showYear(int $year): void
    {
        if ($this->disabled || ! in_array($year, array_column($this->years(), 'value'), true)) {
            return;
        }
        $this->displayMonth($this->boundedMonth(sprintf('%04d-%02d-01', $year, $this->period->month)));
    }

    public function showMonthOfYear(int $month): void
    {
        if ($month >= 1 && $month <= 12) {
            $this->showMonth(sprintf('%04d-%02d', $this->period->year, $month));
        }
    }

    protected function validateOptions(): void
    {
        foreach ([$this->dayLabels, $this->dayClasses] as $decorations) {
            foreach ($decorations as $date => $content) {
                if (! is_string($date) || ! $this->validDate($date) || ! is_string($content)) {
                    throw new InvalidArgumentException(__('ui::errors.calendar_decorations'));
                }
            }
        }
        if (! array_is_list($this->disabledDates)) {
            throw new InvalidArgumentException(__('ui::errors.date_exclusions'));
        }
        foreach (array_filter([$this->min, $this->max, $this->startMonth, $this->endMonth, ...$this->disabledDates], fn ($date) => $date !== null) as $date) {
            if (! is_string($date) || ! $this->validDate($date)) {
                throw new InvalidArgumentException(__('ui::errors.calendar_limits'));
            }
        }
        if ($this->min !== null && $this->max !== null && $this->min > $this->max) {
            throw new InvalidArgumentException(__('ui::errors.calendar_range'));
        }
        if (! in_array($this->monthLabelFormat, ['short', 'long'], true) || ($this->navigationStart !== null && $this->navigationEnd !== null && $this->navigationStart > $this->navigationEnd)) {
            throw new InvalidArgumentException(__('ui::errors.calendar_month_options'));
        }
        if (($this->multiple && $this->range) || $this->numberOfMonths < 1 || $this->numberOfMonths > 12 || $this->weekStartsOn < 0 || $this->weekStartsOn > 6 || ! in_array($this->captionLayout, ['label', 'dropdown'], true)) {
            throw new InvalidArgumentException(__('ui::errors.calendar_options'));
        }
    }

    /** Latest possible first month when several months are displayed together. */
    #[Computed]
    public function lastPageMonth(): ?string
    {
        if ($this->navigationEnd === null) {
            return null;
        }
        $last = CarbonImmutable::parse($this->navigationEnd)->startOfMonth()->subMonths($this->numberOfMonths - 1);

        return max($last->toDateString(), $this->navigationStart ?? '0001-01-01');
    }

    private function boundedMonth(string $month): string
    {
        return max($this->navigationStart ?? '0001-01-01', min($month, $this->lastPageMonth ?? $month));
    }

    private function displayMonth(string $month): void
    {
        if ($month !== $this->month) {
            $this->month = $month;
            unset($this->period, $this->days, $this->months, $this->years);
        }
    }

    public function rendering(): void
    {
        $this->validateOptions();
        $this->displayMonth($this->boundedMonth($this->period->toDateString()));
    }

    public function render(): View
    {
        return view('ui::livewire.calendar');
    }
}
