<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Js;
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

    public function fieldId(): string
    {
        return $this->settings['fieldId'] ?? parent::fieldId();
    }

    public function stateExpression(): string
    {
        if ($this->modelPath !== null) {
            return '$wire.$parent.$get('.Js::from($this->modelPath).')';
        }

        return $this->mode === 'controlled' ? '$wire.externalValue' : '$wire.value';
    }

    /** @param list<mixed> $disabled */
    public function restrictTabs(array $disabled): void
    {
        if ($this->kind !== 'tabs' || $this->mode === 'controlled' || ! in_array($this->current, $disabled, true)) {
            return;
        }
        $available = array_filter($this->settings['values'], static fn ($value) => ! in_array($value, $disabled, true));
        $this->changeValue(array_values($available)[0] ?? null);
    }

    public function selectPanel(mixed $selection): void
    {
        if ($this->kind === 'tabs' ? ! in_array($selection, $this->settings['values'], true) : ! is_bool($selection)) {
            return;
        }
        $this->changeValue($selection);
    }

    public function toggleDisclosure(): void
    {
        if (in_array($this->kind, ['collapsible', 'trace'], true)) {
            $this->changeValue(! $this->current);
        }
    }

    public function advance(int $direction): void
    {
        if ($this->kind !== 'tour' || $this->disabled || $this->readonly || ! $this->current || ! in_array($direction, [-1, 1], true)) {
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
