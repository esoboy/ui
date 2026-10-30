<?php

/*
 * Transfer: two lists, the value is the right one.
 */

it('splits the options between the lists by value', function () {
    $html = (string) $this->blade(<<<'BLADE'
        <x-ui::transfer wire:model="roles" name="roles" :default-value="['b']" searchable :titles="['All', 'Picked']" :options="[
            'a' => 'Alpha',
            'b' => 'Beta',
            ['value' => 'c', 'label' => 'Gamma', 'disabled' => true],
        ]" />
        BLADE);

    expect($html)
        ->toContain('<eso-transfer data-slot="transfer" data-value="[&quot;b&quot;]" data-name="roles" wire:model="roles">')
        ->toContain('<div role="group" data-slot="transfer-list" data-list="source" aria-label="All">')
        ->toContain('<div role="group" data-slot="transfer-list" data-list="target" aria-label="Picked">')
        ->toContain('<span data-slot="transfer-count">0/2</span>')
        ->toContain('<span data-slot="transfer-count">0/1</span>')
        ->toContain('<input type="search" data-slot="input-group-control" data-transfer-search placeholder="Search"')
        ->toContain('data-transfer-move="target" aria-label="Move to selected" disabled="disabled"')
        ->toContain('data-transfer-move="source" aria-label="Move to available" disabled="disabled"')
        ->toMatch('/data-value="c" data-index="2" wire:key="transfer-c">\s*<input type="checkbox" data-slot="checkbox"\s+disabled>/')
        ->toContain('<input type="hidden" name="roles[]" value="b">')
        ->and(strpos($html, 'Alpha'))->toBeLessThan(strpos($html, 'data-list="target"'))
        ->and(strpos($html, 'Beta'))->toBeGreaterThan(strpos($html, 'data-list="target"'));
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
        ->assertSeeInOrder(['</eso-transfer>', 'Pick a role.'], false);
});
