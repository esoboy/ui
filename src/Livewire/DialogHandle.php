<?php

namespace Esoboy\Ui\Livewire;

use Livewire\Component;

/**
 * Ui::dialog('edit')->open() / ->close().
 */
final class DialogHandle
{
    public function __construct(
        private readonly Component $component,
        private readonly string $name,
    ) {}

    public function open(): void
    {
        $this->component->dispatch('ui-dialog', name: $this->name, open: true);
    }

    public function close(): void
    {
        $this->component->dispatch('ui-dialog', name: $this->name, open: false);
    }
}
