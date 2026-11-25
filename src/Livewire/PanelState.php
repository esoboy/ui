<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;

class PanelState extends ValueControl
{
    #[Locked]
    public string $kind;

    /** @var array<string, mixed> */
    #[Reactive]
    public array $settings = [];

    #[Locked]
    public ?int $index = null;

    public function advance(int $direction): void
    {
        if ($this->kind !== 'tour' || ! $this->current || ! in_array($direction, [-1, 1], true)) {
            return;
        }
        $steps = array_values($this->settings['steps']);
        $position = array_search($this->index, $steps, true);
        $next = max(0, ($position === false ? 0 : $position) + $direction);
        if (isset($steps[$next])) {
            $this->index = $steps[$next];
        } else {
            $this->changeValue(false);
        }
    }

    public function render(): View
    {
        if ($this->kind === 'tabs' && ! in_array($this->current, $this->settings['values'], true) && $this->mode !== 'controlled') {
            $this->value = $this->settings['values'][0] ?? null;
            unset($this->current);
        }
        if ($this->kind === 'tour' && ! in_array($this->index, $this->settings['steps'], true)) {
            $this->index = $this->settings['steps'][0] ?? null;
        }
        $template = match ($this->kind) {
            'tabs', 'tour', 'sidebar', 'collapsible' => $this->kind,
            'trace' => 'trace-disclosure',
            default => throw new \LogicException("Unknown panel [{$this->kind}]."),
        };

        return view('ui::livewire.panels.'.$template, $this->settings);
    }
}
