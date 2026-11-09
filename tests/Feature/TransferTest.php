<?php

/*
 * Transfer: two lists, the value is the right one.
 */

it('splits the options between the lists by value', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::transfer wire:model="roles" name="roles" :value="['b']" searchable :titles="['All', 'Picked']" :options="[
            'a' => 'Alpha',
            'b' => 'Beta',
            ['value' => 'c', 'label' => 'Gamma', 'disabled' => true],
        ]" />
        BLADE);

    expect($html)
        ->toContainMarkup('<eso-transfer data-slot="transfer">')
        ->toContainMarkup('<div role="group" data-slot="transfer-list" data-list="source" aria-label="All">')
        ->toContainMarkup('<div role="group" data-slot="transfer-list" data-list="target" aria-label="Picked">')
        ->toContainMarkup('<span data-slot="transfer-count">0/2</span>')
        ->toContainMarkup('<span data-slot="transfer-count">0/1</span>')
        ->toContainMarkup('<input type="search" data-slot="input-group-control" data-transfer-search="data-transfer-search" placeholder="Search">')
        ->toContainMarkup('<button data-transfer-move="target" aria-label="Move to selected" disabled="disabled">')
        ->toContainMarkup('<button data-transfer-move="source" aria-label="Move to available" disabled="disabled">')
        ->toContainMarkup('<input type="checkbox" value="c" disabled>')
        ->toContainMarkup('<input type="hidden" name="roles[]" value="b">')
        ->and(strpos($html, '<span>Alpha</span>'))->toBeLessThan(strpos($html, 'data-list="target"'))
        ->and(strpos($html, '<span>Beta</span>'))->toBeGreaterThan(strpos($html, 'data-list="target"'));
});

it('titles the lists from translations and leaves out search by default', function () {
    $this->blade('<x-ui::transfer :options="[\'a\' => \'Alpha\']" />')
        ->assertSee('aria-label="Available"', false)
        ->assertSee('aria-label="Selected"', false)
        ->assertDontSee('data-transfer-search', false);
});

it('validates a transfer inside a field', function () {
    $this->withViewErrors(['roles' => 'Pick a role.'])
        ->blade('<x-ui::field><x-ui::transfer id="roles" wire:model="roles" /></x-ui::field>')
        ->assertSee('aria-invalid="true" aria-describedby="roles-error"', false)
        ->assertSeeMarkupInOrder(['</eso-transfer>', 'Pick a role.']);
});
