<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Tour extends Component
{
    /** @var list<array{target: string, title: string, description: string, side: string, align: string}> */
    #[Reactive]
    public array $steps = [];

    #[Locked]
    public bool $blocking = true;

    #[Locked]
    public ?string $actionsView = null;

    #[Locked]
    public ?int $step = null;

    public function start(int $step = 0): void
    {
        if (isset($this->steps[$step])) {
            $this->step = $step;
            $this->dispatch('tour-change', step: $step);
        }
    }

    public function previous(): void
    {
        if ($this->step !== null) {
            $this->start($this->step - 1);
        }
    }

    public function next(): void
    {
        if ($this->step !== null) {
            $this->start($this->step + 1);
        }
    }

    public function finish(): void
    {
        if ($this->step === count($this->steps) - 1) {
            $this->end(true);
        }
    }

    public function close(): void
    {
        $this->end(false);
    }

    private function end(bool $finished): void
    {
        if ($this->step !== null) {
            $this->dispatch('tour-close', step: $this->step, finished: $finished);
            $this->step = null;
        }
    }

    public function render(): View
    {
        return view('ui::livewire.tour', ['current' => $this->steps[$this->step] ?? null]);
    }
}
