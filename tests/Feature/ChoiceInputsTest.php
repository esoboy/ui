<?php

use Esoboy\Ui\Tests\Fixtures\Pickers;
use Livewire\Livewire;

/*
 * Toggle, Toggle Group, Slider and Input OTP.
 */

it('renders a toggle as a label around a checkbox', function () {
    $this->blade('<x-ui::toggle variant="outline" size="sm" aria-label="Bold" wire:model="bold" class="px-4" default-pressed>B</x-ui::toggle>')
        ->assertSee('<label data-slot="toggle" data-variant="outline" data-size="sm" class="px-4">', false)
        ->assertSee('<input type="checkbox" checked="checked" aria-label="Bold" wire:model="bold">', false);
});

it('renders a single toggle group as a deselectable radio group', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::toggle-group variant="outline" spacing="0" wire:model="align" default-value="left">
            <x-ui::toggle-group.item value="left">Left</x-ui::toggle-group.item>
            <x-ui::toggle-group.item value="right" variant="default">Right</x-ui::toggle-group.item>
        </x-ui::toggle-group>
        BLADE);

    $view->assertSee('<eso-radio-group role="group" data-slot="toggle-group" data-variant="outline" data-size="default" data-spacing="0" data-orientation="horizontal" data-deselectable="data-deselectable" style="--gap: 0;" wire:model="align">', false)
        ->assertSee('<label data-slot="toggle-group-item" data-variant="outline" data-size="default" data-spacing="0">', false)
        ->assertSee('<input type="radio" value="left" checked="checked">', false)
        ->assertSee('<label data-slot="toggle-group-item" data-variant="default"', false)
        ->assertSee('<input type="radio" value="right">', false);
});

it('renders a multiple toggle group as a checkbox group', function () {
    $view = $this->blade(<<<'BLADE'
        <x-ui::toggle-group type="multiple" :default-value="['b', 'i']">
            <x-ui::toggle-group.item value="b">B</x-ui::toggle-group.item>
            <x-ui::toggle-group.item value="i">I</x-ui::toggle-group.item>
            <x-ui::toggle-group.item value="u">U</x-ui::toggle-group.item>
        </x-ui::toggle-group>
        BLADE);

    $view->assertSee('<eso-checkbox-group role="group" data-slot="toggle-group"', false)
        ->assertDontSee('data-deselectable', false)
        ->assertSee('<input type="checkbox" value="b" checked="checked">', false)
        ->assertSee('<input type="checkbox" value="i" checked="checked">', false)
        ->assertSee('<input type="checkbox" value="u">', false);
});

it('renders one native range input per slider value', function () {
    $html = (string) $this->blade('<x-ui::slider :default-value="[25, 75]" :step="5" name="price" aria-label="Price" />');

    expect($html)
        ->toContain('<eso-slider data-slot="slider" data-orientation="horizontal" name="price">')
        ->toContain('style="--start: 25; --end: 75"')
        ->and(substr_count($html, 'data-slot="slider-thumb"'))->toBe(2)
        ->and($html)->toContain('<input type="range" data-slot="slider-thumb" min="0" max="100" step="5" value="25" aria-orientation="horizontal" aria-label="Price">');
});

it('fills a single slider from the start', function () {
    $this->blade('<x-ui::slider default-value="40" :min="0" :max="200" orientation="vertical" disabled />')
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

    $view->assertSee('<eso-input-otp data-slot="input-otp" class="justify-center">', false)
        ->assertSee('<div data-slot="input-otp-group">', false)
        ->assertSee('<div data-slot="input-otp-slot" data-index="0"></div>', false)
        ->assertSee('<div data-slot="input-otp-separator" role="separator">', false)
        ->assertSee('<input type="text" data-slot="input-otp-input" maxlength="4" autocomplete="one-time-code" spellcheck="false" wire:model="code" inputmode="numeric">', false);
});

it('validates an input otp inside a field', function () {
    $this->withViewErrors(['code' => 'Invalid code.'])
        ->blade('<x-ui::field><x-ui::input-otp id="code" wire:model="code"><x-ui::input-otp.slot :index="0" /></x-ui::input-otp></x-ui::field>')
        ->assertSee('aria-invalid="true" aria-describedby="code-error"', false)
        ->assertSeeInOrder(['</eso-input-otp>', 'Invalid code.'], false);
});
