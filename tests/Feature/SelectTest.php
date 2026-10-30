<?php

it('renders a select with its parts linked by id', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::select wire:model="fruit" name="fruit" default-value="apple">
            <x-ui::select.trigger size="sm" class="w-48">
                <x-ui::select.value placeholder="Select a fruit" />
            </x-ui::select.trigger>
            <x-ui::select.content :align-item-with-trigger="false" side="top">
                <x-ui::select.group>
                    <x-ui::select.label>Fruits</x-ui::select.label>
                    <x-ui::select.item value="apple">Apple</x-ui::select.item>
                    <x-ui::select.item value="kiwi" disabled>Kiwi</x-ui::select.item>
                </x-ui::select.group>
                <x-ui::select.separator />
            </x-ui::select.content>
        </x-ui::select>
        BLADE);

    expect($html)
        ->toContain('<eso-select data-slot="select" data-value="apple" data-name="fruit" wire:model="fruit">')
        ->toContain('<button type="button" data-slot="select-trigger" data-size="sm" aria-haspopup="listbox" aria-expanded="false" command="toggle-popover" commandfor="" class="w-48">')
        ->toContain('<span data-slot="select-value" data-placeholder="Select a fruit">Select a fruit</span>')
        ->toContain('<div popover="auto" role="listbox" tabindex="-1" data-slot="select-content" data-align-trigger="false" data-side="top"')
        ->toContain('<div role="group" data-slot="select-group">')
        ->toContain('<div data-slot="select-label">Fruits</div>')
        ->toContain('<div role="option" tabindex="-1" data-slot="select-item" data-value="apple" aria-selected="false">')
        ->toContain('data-value="kiwi" aria-selected="false" aria-disabled="true"')
        ->toContain('<span data-slot="select-item-text">Apple</span>')
        ->toContain('<div role="separator" data-slot="select-separator"></div>')
        ->toContain('<input type="hidden" name="fruit" value="apple">');
});

it('aligns the list with the trigger by default', function () {
    $this->blade('<x-ui::select><x-ui::select.trigger>x</x-ui::select.trigger><x-ui::select.content>y</x-ui::select.content></x-ui::select>')
        ->assertSee('data-align-trigger="true"', false);
});

it('validates a select inside a field', function () {
    $this->withViewErrors(['fruit' => 'Pick a fruit.'])
        ->blade('<x-ui::field><x-ui::select id="fruit" wire:model="fruit"><x-ui::select.trigger>x</x-ui::select.trigger></x-ui::select></x-ui::field>')
        ->assertSee('<eso-select data-slot="select" aria-invalid="true" aria-describedby="fruit-error" id="fruit" wire:model="fruit">', false)
        ->assertSeeInOrder(['</eso-select>', 'Pick a fruit.'], false);
});
