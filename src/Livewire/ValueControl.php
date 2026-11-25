<?php

namespace Esoboy\Ui\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

/** @property-read mixed $current */
abstract class ValueControl extends Component
{
    use ForwardsControlAttributes;

    #[Modelable]
    public mixed $value = null;

    #[Locked]
    public string $mode = 'internal';

    #[Reactive]
    public mixed $externalValue = null;

    #[Reactive]
    public bool $disabled = false;

    #[Reactive]
    public bool $readonly = false;

    #[Reactive]
    public string $applicationLocale = '';

    public function updatedApplicationLocale(string $locale): void
    {
        app()->setLocale($locale);
    }

    public function mount(mixed $initialValue = null): void
    {
        if ($this->mode === 'internal') {
            $this->value = $initialValue;
        }
    }

    #[Computed]
    public function current(): mixed
    {
        return $this->mode === 'controlled' ? $this->externalValue : $this->value;
    }

    protected function changeValue(mixed $value): void
    {
        if ($this->disabled || $this->readonly) {
            return;
        }

        if ($this->mode !== 'controlled') {
            $this->value = $value;
        }

        unset($this->current);
        $this->dispatch('value-changed', value: $value);
    }

    abstract public function render(): View;
}
