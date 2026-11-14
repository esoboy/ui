<?php

it('renders an input group with addons', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::input-group>
            <x-ui::input-group.input placeholder="Search" wire:model.live="q" />
            <x-ui::input-group.addon><x-ui::input-group.text>https://</x-ui::input-group.text></x-ui::input-group.addon>
            <x-ui::input-group.addon align="inline-end"><x-ui::input-group.button size="icon-xs">x</x-ui::input-group.button></x-ui::input-group.addon>
        </x-ui::input-group>
        BLADE);

    $view->assertSeeMarkup('<div role="group" data-slot="input-group">')
        ->assertSeeMarkup('<input type="text" data-slot="input-group-control" placeholder="Search" wire:model.live="q">')
        ->assertSeeMarkup('<div role="group" data-slot="input-group-addon" data-align="inline-start"><span data-slot="input-group-text">https://</span></div>')
        ->assertSeeMarkup('<div role="group" data-slot="input-group-addon" data-align="inline-end">')
        ->assertSeeMarkup('<button type="button" data-slot="button" data-variant="ghost" data-size="default" data-input-group-size="icon-xs">x</button>');
});

it('uses the four Nova input group button sizes', function (string $size) {
    $this->blade('<x-ui::input-group.button :size="$size">Action</x-ui::input-group.button>', ['size' => $size])
        ->assertSeeMarkup('<button data-slot="button" data-size="default" data-input-group-size="'.$size.'">Action</button>');
})->with(['xs', 'sm', 'icon-xs', 'icon-sm']);

it('defaults the input group button to xs', function () {
    $this->blade('<x-ui::input-group.button>Action</x-ui::input-group.button>')
        ->assertSeeMarkup('<button data-slot="button" data-size="default" data-input-group-size="xs">Action</button>');
});

it('renders a textarea control', function () {
    $this->blade('<x-ui::input-group><x-ui::input-group.textarea rows="3">Hi</x-ui::input-group.textarea></x-ui::input-group>')
        ->assertSeeMarkup('<textarea data-slot="input-group-control" rows="3">Hi</textarea>');
});

it('shows the error after the group, not inside it', function (string $control) {
    $html = (string) $this->withViewErrors(['website' => 'Must be a URL.'])
        ->blade("<x-ui::field><x-ui::input-group>{$control}</x-ui::input-group></x-ui::field>");

    expect($html)
        ->toContain('aria-invalid="true" aria-describedby="website-error"')
        ->toMatch('#</div>\s*<div role="alert" data-slot="field-error" id="website-error">\s*Must be a URL.\s*</div>\s*</div>#');

    expect(substr_count($html, 'Must be a URL.'))->toBe(1);
})->with([
    'input' => ['<x-ui::input-group.input id="website" wire:model="website" />'],
    'textarea' => ['<x-ui::input-group.textarea id="website" wire:model="website" />'],
]);

it('does not validate outside a field', function () {
    $this->withViewErrors(['website' => 'Must be a URL.'])
        ->blade('<x-ui::input-group><x-ui::input-group.input wire:model="website" /></x-ui::input-group>')
        ->assertDontSee('aria-invalid', false)
        ->assertDontSee('Must be a URL.');
});

it('respects show-error on the field', function () {
    $this->withViewErrors(['website' => 'Must be a URL.'])
        ->blade('<x-ui::field :show-error="false"><x-ui::input-group><x-ui::input-group.input wire:model="website" /></x-ui::input-group></x-ui::field>')
        ->assertSee('aria-invalid="true"', false)
        ->assertDontSee('Must be a URL.');
});

it('does not leak one group\'s error into the next', function () {
    $html = (string) $this->withViewErrors(['a' => 'Error A.'])
        ->blade(<<<'BLADE'
            <x-ui::field><x-ui::input-group><x-ui::input-group.input wire:model="a" /></x-ui::input-group></x-ui::field>
            <x-ui::input-group><x-ui::input-group.text>no control</x-ui::input-group.text></x-ui::input-group>
            BLADE);

    expect(substr_count($html, 'Error A.'))->toBe(1);
});

it('renders a character count and inherited limit on the server', function () {
    $this->blade(<<<'BLADE'
        <x-ui::input-group>
            <x-ui::input-group.textarea wire:model="bio" maxlength="280" />
            <x-ui::input-group.addon align="block-end"><x-ui::input-group.count /></x-ui::input-group.addon>
        </x-ui::input-group>
        BLADE)
        ->assertSeeMarkup('<eso-input-group-count data-slot="input-group-count" data-max="280">0/280 characters</eso-input-group-count>');

    app()->setLocale('zh_CN');
    $this->blade('<x-ui::input-group.count :max="140" />')
        ->assertSeeMarkup('<eso-input-group-count data-max="140">0/140 字</eso-input-group-count>');
});

it('renders the initial Unicode character count and inherited limit in PHP', function () {
    $this->blade('<x-ui::input-group><x-ui::input-group.textarea maxlength="10">😀ab</x-ui::input-group.textarea><x-ui::input-group.count /></x-ui::input-group>')
        ->assertSee('3/10 characters');
    $html = (string) $this->blade('<x-ui::input-group><x-ui::input-group.input value="Hello" /><x-ui::input-group.count /></x-ui::input-group><x-ui::input-group><x-ui::input-group.input value="Hi" /><x-ui::input-group.count /></x-ui::input-group>');
    expect($html)->toContain('5 characters')->toContain('2 characters')
        ->and(count(array_unique(idsOf($html, 'id'))))->toBe(2);
});
