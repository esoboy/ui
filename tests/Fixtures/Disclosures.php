<?php

namespace Esoboy\Ui\Tests\Fixtures;

use Livewire\Component;

/**
 * A collapsible bound to a property.
 */
class Disclosures extends Component
{
    public bool $open = true;

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                <x-ui::collapsible wire:model="open">
                    <x-ui::collapsible.trigger>Toggle</x-ui::collapsible.trigger>
                    <x-ui::collapsible.content>Details</x-ui::collapsible.content>
                </x-ui::collapsible>
            </div>
            BLADE;
    }
}
