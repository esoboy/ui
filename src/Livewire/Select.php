<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Reactive;

/**
 * @property-read list<array{value: string|int|null, label: string, disabled: bool}> $formDefaults
 */
class Select extends PopupOptionControl
{
    #[Reactive]
    public bool $classic = false;

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

    /** @return list<array{value: string|int|null, label: string, disabled: bool}> */
    #[Computed]
    public function formDefaults(): array
    {
        $defaults = [];
        foreach ($this->selectionValues($this->initialSelection) as $id) {
            if ($option = $this->optionFor($id)) {
                $defaults[] = $option;
            }
        }

        return $defaults;
    }

    /** @return list<array{value: string|int|null, label: string, disabled: bool}> */
    #[Computed]
    public function formChoices(): array
    {
        return [...$this->selectedChoices, ...array_filter($this->choices, fn ($option) => $option['value'] !== null && ! $this->isSelected($option['value']))];
    }

    public function resetSelection(): void
    {
        $valid = array_column($this->formDefaults, 'value');
        $this->changeValue($this->multiple ? $valid : ($valid[0] ?? null));
    }

    public function updatedValue(mixed $value): void
    {
        if ($this->mode !== 'controlled') {
            $this->dispatch('value-changed', value: $value);
        }
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
