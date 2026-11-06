<?php

namespace Esoboy\Ui\Tests\Fixtures;

use Livewire\Component;

/**
 * Controls that paint their initial state from the bound Livewire value.
 */
class Pickers extends Component
{
    public int $volume = 40;

    /** @var list<int> */
    public array $price = [20, 80];

    public string $fruit = 'banana';

    /** @var array{from: string, to: string} */
    public array $range = ['from' => '2026-01-05', 'to' => '2026-01-09'];

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                <x-ui::slider id="volume" wire:model="volume" />
                <x-ui::slider id="price" wire:model="price" />
                <x-ui::select id="fruit" wire:model="fruit">
                    <x-ui::select.trigger><x-ui::select.value placeholder="Fruit" /></x-ui::select.trigger>
                    <x-ui::select.content>
                        <x-ui::select.item value="banana">Banana</x-ui::select.item>
                    </x-ui::select.content>
                </x-ui::select>
                <x-ui::calendar id="range" mode="range" wire:model="range" />
            </div>
            BLADE;
    }
}
