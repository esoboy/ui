<?php

namespace Esoboy\Ui\Tests\Fixtures;

use Livewire\Component;

class Menus extends Component
{
    public function render(): string
    {
        return <<<'BLADE'
            <div>
                <x-ui::popover>
                    <x-ui::popover.trigger>Open</x-ui::popover.trigger>
                    <x-ui::popover.content>Content</x-ui::popover.content>
                </x-ui::popover>
                <x-ui::dropdown-menu>
                    <x-ui::dropdown-menu.trigger>Menu</x-ui::dropdown-menu.trigger>
                    <x-ui::dropdown-menu.content>
                        <x-ui::dropdown-menu.item>Item</x-ui::dropdown-menu.item>
                    </x-ui::dropdown-menu.content>
                </x-ui::dropdown-menu>
            </div>
            BLADE;
    }
}
