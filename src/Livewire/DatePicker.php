<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\ClockTime;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;

class DatePicker extends Calendar
{
    #[Locked]
    public bool $withTime = false;

    #[Reactive]
    public bool $seconds = false;

    #[Reactive]
    public bool $clearable = false;

    #[Locked]
    public string $contentTarget = '';

    public ?string $activeUnit = null;

    /** @return list<string> Calendar days, independent of the clock precision. */
    #[Computed]
    public function selected(): array
    {
        if (! $this->withTime) {
            return parent::selected();
        }
        $value = $this->current;
        if ($value === null || $value === '') {
            return [];
        }
        if (! is_string($value) || ! str_contains($value, 'T')) {
            throw new InvalidArgumentException(__('ui::errors.datetime_separator'));
        }
        [$date, $time] = explode('T', $value, 2);
        if (! $this->validDate($date) || ClockTime::read($time, $this->seconds) === null) {
            throw new InvalidArgumentException(__('ui::errors.datetime_value'));
        }

        return [$date];
    }

    private function time(): ?string
    {
        return $this->withTime && $this->selected !== [] ? substr($this->current, 11) : null;
    }

    public function dateSelection(string $date): mixed
    {
        if (! $this->withTime) {
            return parent::dateSelection($date);
        }

        return $this->validDate($date) && $this->allowed($date)
            ? $date.'T'.($this->time() ?? ClockTime::midnight($this->seconds))
            : $this->current;
    }

    public function updatedValue(mixed $value): void
    {
        parent::updatedValue($value);
        $this->notifyCompletedSelection($value);
    }

    protected function changeValue(mixed $value): void
    {
        parent::changeValue($value);
        if (! $this->disabled && ! $this->readonly) {
            $this->notifyCompletedSelection($value);
        }
    }

    private function notifyCompletedSelection(mixed $value): void
    {
        if (! $this->withTime && ! $this->multiple && $value !== null && (! $this->range || (is_array($value) && count($value) === 2))) {
            $this->dispatch('date-picked')->self();
        }
    }

    public function timeSelection(string $unit, int $number): ?string
    {
        if (! $this->withTime || $this->selected === [] || ! $this->allowed($this->selected[0])) {
            return null;
        }
        $next = ClockTime::replace($this->time(), $this->seconds, $unit, $number);

        return $next === null ? null : $this->selected[0].'T'.$next;
    }

    public function chooseTime(string $unit, int $number): void
    {
        $this->activeUnit = null;
        if (($next = $this->timeSelection($unit, $number)) !== null) {
            $this->changeValue($next);
        }
    }

    public function clear(): void
    {
        if ($this->clearable) {
            $this->changeValue($this->multiple || $this->range ? [] : null);
            unset($this->selected, $this->days);
        }
    }

    public function render(): View
    {
        return view('ui::livewire.date-picker', [
            'picker' => $this->withTime ? 'datetime-picker' : 'date-picker',
            'time' => $this->time(),
        ]);
    }
}
