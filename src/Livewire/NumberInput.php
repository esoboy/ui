<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class NumberInput extends Component
{
    #[Modelable]
    public mixed $value = null;

    /** @var array<string, mixed> */
    #[Reactive]
    public array $settings = [];

    public function step(int $direction): void
    {
        if (($this->settings['disabled'] ?? false) || ! in_array($direction, [-1, 1], true)) {
            return;
        }

        $min = $this->settings['min'] ?? null;
        $max = $this->settings['max'] ?? null;
        $step = abs((float) ($this->settings['step'] ?? 1)) ?: 1;
        $next = is_numeric($this->value)
            ? round((float) $this->value + $direction * $step, PHP_FLOAT_DIG - 1)
            : ($min ?? 0);
        $this->value = min($max ?? INF, max($min ?? -INF, $next));
        $this->dispatch('ui-model-changed')->self();
    }

    public function render(): View
    {
        return view('ui::livewire.number-input');
    }
}
