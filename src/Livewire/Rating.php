<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class Rating extends Component
{
    #[Modelable]
    public mixed $value = null;

    /** @var array<string, mixed> */
    #[Reactive]
    public array $settings = [];

    public function choose(int $score, bool $toggle = true): void
    {
        if (($this->settings['disabled'] ?? false) || $score < 1 || $score > ($this->settings['max'] ?? 5)) {
            return;
        }

        $next = $toggle && is_numeric($this->value) && (float) $this->value === (float) $score ? null : $score;
        if ($next !== $this->value) {
            $this->value = $next;
            $this->dispatch('ui-model-changed')->self();
        }
    }

    public function render(): View
    {
        return view('ui::livewire.rating');
    }
}
