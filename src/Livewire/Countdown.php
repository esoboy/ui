<?php

namespace Esoboy\Ui\Livewire;

use Esoboy\Ui\Support\Countdown as Clock;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Date;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Countdown extends Component
{
    #[Locked]
    public ?string $name = null;

    #[Locked]
    public bool $verification = false;

    #[Locked]
    public int $seconds = 60;

    #[Locked]
    public string $format = 'HH:mm:ss';

    #[Locked]
    public int $deadline = 0;

    #[Locked]
    public bool $finished = true;

    public function mount(int $remaining = 0): void
    {
        if (! $this->verification && $remaining > 0) {
            $this->deadline = $this->now() + $remaining;
            $this->finished = false;
        }
    }

    public function start(?int $seconds = null): void
    {
        $seconds = max(0, min($seconds ?? $this->seconds, intdiv(PHP_INT_MAX - $this->now(), 1000)));
        $this->deadline = $this->now() + $seconds * 1000;
        $this->finished = false;
        $this->tick();
    }

    public function restore(int $deadline): void
    {
        $this->deadline = max(0, $deadline);
        $this->finished = $this->deadline <= $this->now();
    }

    public function tick(): void
    {
        if (! $this->finished && $this->deadline <= $this->now()) {
            $this->finished = true;
            $this->dispatch('finish');
        }
    }

    public function render(): View
    {
        $remaining = max(0, $this->deadline - $this->now());
        $delay = $this->finished ? 0 : Clock::nextUpdate($remaining, $this->verification ? 's' : $this->format);

        return view('ui::livewire.countdown', compact('remaining', 'delay'));
    }

    private function now(): int
    {
        return Date::now()->getTimestampMs();
    }
}
