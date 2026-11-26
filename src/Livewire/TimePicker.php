<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\ClockTime;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;

class TimePicker extends ValueControl
{
    use HasControlSize;

    #[Reactive]
    public bool $seconds = false;

    #[Reactive]
    public bool $clearable = false;

    #[Locked]
    public string $contentTarget = '';

    public ?string $activeUnit = null;

    public function timeSelection(string $unit, int $number): ?string
    {
        return ClockTime::replace(ClockTime::read($this->current, $this->seconds), $this->seconds, $unit, $number);
    }

    public function chooseTime(string $unit, int $number): void
    {
        $this->activeUnit = null;
        $next = $this->timeSelection($unit, $number);
        if ($next !== null) {
            $this->changeValue($next);
        }
    }

    public function clear(): void
    {
        if ($this->clearable) {
            $this->changeValue(null);
        }
    }

    public function render(): View
    {
        return view('ui::livewire.time-picker', ['time' => ClockTime::read($this->current, $this->seconds)]);
    }
}
