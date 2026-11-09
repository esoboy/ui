<?php

use Esoboy\Ui\Tests\Fixtures\Pickers;
use Livewire\Livewire;

/*
 * Toggle, Toggle Group, Slider and Input OTP.
 */

it('renders a toggle as a label around a checkbox', function () {
    $this->blade('<x-ui::toggle variant="outline" size="sm" aria-label="Bold" wire:model="bold" class="px-4" checked>B</x-ui::toggle>')
        ->assertSeeMarkup('<label data-slot="toggle" data-variant="outline" data-size="sm" class="px-4">')
        ->assertSeeMarkup('<input type="checkbox" checked="checked" aria-label="Bold" wire:model="bold">');
});

it('renders a single toggle group as a deselectable radio group', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::toggle-group variant="outline" spacing="0" wire:model="align" value="left">
            <x-ui::toggle-group.item value="left">Left</x-ui::toggle-group.item>
            <x-ui::toggle-group.item value="right" variant="default">Right</x-ui::toggle-group.item>
        </x-ui::toggle-group>
        BLADE);

    $view->assertSeeMarkup('<div role="group" data-slot="toggle-group" data-variant="outline" data-size="default" data-spacing="0" data-orientation="horizontal" data-deselectable="data-deselectable" style="--gap: 0;">')
        ->assertSeeMarkup('<label data-slot="toggle-group-item" data-variant="outline" data-size="default" data-spacing="0">')
        ->assertSeeMarkup('<input type="radio" value="left" checked="checked">')
        ->assertSee('<label data-slot="toggle-group-item" data-variant="default"', false)
        ->assertSeeMarkup('<input type="radio" value="right">');
});

it('renders a multiple toggle group as a checkbox group', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::toggle-group multiple :value="['b', 'i']">
            <x-ui::toggle-group.item value="b">B</x-ui::toggle-group.item>
            <x-ui::toggle-group.item value="i">I</x-ui::toggle-group.item>
            <x-ui::toggle-group.item value="u">U</x-ui::toggle-group.item>
        </x-ui::toggle-group>
        BLADE);

    $view->assertSeeMarkup('<div role="group" data-slot="toggle-group">')
        ->assertDontSee('data-deselectable', false)
        ->assertSeeMarkup('<input type="checkbox" value="b" checked="checked">')
        ->assertSeeMarkup('<input type="checkbox" value="i" checked="checked">')
        ->assertSeeMarkup('<input type="checkbox" value="u">');
});

it('renders one native range input per slider value', function () {
    $html = (string) $this->blade('<x-ui::slider :value="[25, 75]" :step="5" name="price" aria-label="Price" />');

    expect($html)
        ->toContainMarkup('<eso-slider data-slot="slider" data-orientation="horizontal">')
        ->toContain('style="--start: 25; --end: 75"')
        ->and(substr_count($html, 'data-slot="slider-thumb"'))->toBe(2)
        ->and($html)->toContainMarkup('<input type="range" data-slot="slider-thumb" min="0" max="100" step="5" value="25" name="price[]" aria-orientation="horizontal" aria-label="Price">');
});

it('fills a single slider from the start', function () {
    $this->blade('<x-ui::slider value="40" :min="0" :max="200" orientation="vertical" disabled />')
        ->assertSee('data-orientation="vertical" data-disabled="data-disabled"', false)
        ->assertSee('style="--start: 0; --end: 20"', false)
        ->assertSee('aria-orientation="vertical" disabled="disabled">', false);
});

it('paints Livewire values on the server', function () {
    $html = Livewire::test(Pickers::class)->html();

    expect($html)
        ->toContain('style="--start: 0; --end: 40"')
        ->toContain('style="--start: 20; --end: 80"')
        ->toContain('data-value="banana"')
        ->toContain('data-value="{&quot;from&quot;:&quot;2026-01-05&quot;,&quot;to&quot;:&quot;2026-01-09&quot;}"');
});

it('renders an input otp with slots over one input', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::input-otp maxlength="4" wire:model="code" inputmode="numeric" class="justify-center">
            <x-ui::input-otp.group>
                <x-ui::input-otp.slot :index="0" />
                <x-ui::input-otp.slot :index="1" />
            </x-ui::input-otp.group>
            <x-ui::input-otp.separator />
        </x-ui::input-otp>
        BLADE);

    $view->assertSeeMarkup('<eso-input-otp data-slot="input-otp" class="justify-center">')
        ->assertSeeMarkup('<div data-slot="input-otp-group">')
        ->assertSeeMarkup('<div data-slot="input-otp-slot" data-index="0"></div>')
        ->assertSeeMarkup('<div data-slot="input-otp-separator" role="separator">')
        ->assertSeeMarkup('<input type="text" data-slot="input-otp-input" maxlength="4" autocomplete="one-time-code" spellcheck="false" wire:model.live.debounce.100ms="value" inputmode="numeric">');
});

it('validates an input otp inside a field', function () {
    $this->withViewErrors(['code' => 'Invalid code.'])
        ->blade('<x-ui::field><x-ui::input-otp id="code" wire:model="code"><x-ui::input-otp.slot :index="0" /></x-ui::input-otp></x-ui::field>')
        ->assertSee('aria-invalid="true" aria-describedby="code-error"', false)
        ->assertSeeMarkupInOrder(['</eso-input-otp>', 'Invalid code.']);
});
