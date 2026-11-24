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

    public function chooseTime(string $unit, int $number): void
    {
        $next = ClockTime::replace(ClockTime::read($this->current, $this->seconds), $this->seconds, $unit, $number);
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
