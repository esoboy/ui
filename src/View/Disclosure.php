<?php

namespace Esoboy\Ui\View;

use Esoboy\Ui\Support\ControlAttributes;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Livewire\Livewire;

final class Disclosure extends Component
{
    public readonly self $disclosure;

    public function __construct(public readonly bool $open = false, public readonly bool $initialOpen = false)
    {
        $this->disclosure = $this;
    }

    public function isOpen(): bool
    {
        $model = $this->attributes->whereStartsWith('wire:model')->first();

        return (bool) ($model === null
            ? $this->attributes->get('value', $this->open || $this->initialOpen)
            : data_get(Livewire::current(), ControlAttributes::modelPath($model)));
    }

    public function render(): View
    {
        return view($this->componentName === 'ui::trace.collapsible'
            ? 'ui::components.trace.collapsible'
            : 'ui::components.collapsible.index');
    }
}
