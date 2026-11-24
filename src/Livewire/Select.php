<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;

class Select extends PopupOptionControl
{
    #[Reactive]
    public ?string $displayValue = null;

    #[Reactive]
    public bool $required = false;

    #[Locked]
    public mixed $initialSelection = null;

    public function mount(mixed $initialValue = null): void
    {
        parent::mount($initialValue);
        $this->initialSelection = $this->current;
    }

    public function updatedOptions(): void
    {
        $valid = array_column($this->selectedChoices, 'value');
        if ($valid === $this->selected) {
            return;
        }

        $value = $this->multiple ? $valid : ($valid[0] ?? null);
        if ($this->mode !== 'controlled') {
            $this->value = $value;
            unset($this->current, $this->selected, $this->selectedKeys, $this->selectedChoices, $this->label);
        }
        $this->dispatch('value-changed', value: $value);
    }

    public function render(): View
    {
        return view('ui::livewire.select');
    }
}
