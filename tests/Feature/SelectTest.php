<?php

use Livewire\Livewire;

it('renders the new selection on the server and rejects disabled options', function () {
    $choices = '<button data-slot="select-trigger"><span data-slot="select-value">Choose</span></button>'
        .'<div data-slot="select-content"><div data-slot="select-item" data-value="a"><span data-slot="select-item-text">Alpha</span></div>'
        .'<div data-slot="select-item" data-value="b" aria-disabled="true"><span data-slot="select-item-text">Beta</span></div></div>';

    Livewire::test('ui.select', ['markup' => $choices, 'name' => 'choice'])
        ->call('choose', 'a')
        ->assertSeeHtml('data-slot="select-value">Alpha</span>')
        ->assertSet('value', 'a')
        ->call('choose', 'b')
        ->assertSet('value', 'a');
});

it('renders a select with its parts linked by id', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::select wire:model="fruit" name="fruit" value="apple">
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
        ->toContainMarkup('<eso-select data-slot="select" data-value="apple">')
        ->toContainMarkup('<button type="button" data-slot="select-trigger" data-size="sm" aria-haspopup="listbox" aria-expanded="false" command="toggle-popover" class="w-48">')
        ->toContainMarkup('<span data-slot="select-value" data-placeholder="Select a fruit">Apple</span>')
        ->toContain('<div popover="auto" role="listbox" tabindex="-1" data-slot="select-content" data-align-trigger="false" data-side="top"')
        ->toContainMarkup('<div role="group" data-slot="select-group">')
        ->toContainMarkup('<div data-slot="select-label">Fruits</div>')
        ->toContainMarkup('<div role="option" tabindex="-1" data-slot="select-item" data-value="apple" aria-selected="true">')
        ->toContain('data-value="kiwi" aria-selected="false" aria-disabled="true"')
        ->toContainMarkup('<span data-slot="select-item-text">Apple</span>')
        ->toContainMarkup('<div role="separator" data-slot="select-separator"></div>')
        ->toContain('name="fruit"')->toContainMarkup('<option value="apple" selected>Apple</option>');
    expect(idsOf($html, 'id'))->toContain(idsOf($html, 'commandfor')[0]);
});

it('aligns the list with the trigger by default', function () {
    $this->blade('<x-ui::select><x-ui::select.trigger>x</x-ui::select.trigger><x-ui::select.content>y</x-ui::select.content></x-ui::select>')
        ->assertSee('data-align-trigger="true"', false);
});

it('validates a select inside a field', function () {
    $this->withViewErrors(['fruit' => 'Pick a fruit.'])
        ->blade('<x-ui::field><x-ui::select id="fruit" wire:model="fruit"><x-ui::select.trigger>x</x-ui::select.trigger></x-ui::select></x-ui::field>')
        ->assertSeeMarkup('<eso-select data-slot="select" aria-invalid="true" aria-describedby="fruit-error" id="fruit">')
        ->assertSeeMarkupInOrder(['</eso-select>', 'Pick a fruit.']);
});
