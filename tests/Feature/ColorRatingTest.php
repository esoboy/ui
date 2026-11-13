<?php

use Livewire\Livewire;

it('keeps one canonical color for the label, swatches and form value', function () {
    $picker = Livewire::test('ui.color-picker', ['settings' => ['swatches' => ['#f80']]])
        ->call('setColor', '  #F80 ')
        ->assertSet('value', '#ff8800')
        ->assertSee('#ff8800')
        ->assertSee('aria-pressed="true"', false)
        ->call('setColor', 'red')
        ->assertSet('value', '#ff8800');

    $picker->call('setColor', '')->assertSet('value', null)->assertDontSee('aria-pressed="true"', false);
});

it('selects and clears a valid rating on the server', function () {
    Livewire::test('ui.rating', ['settings' => ['max' => 3]])
        ->call('choose', 2)->assertSet('value', 2)
        ->call('choose', 4)->assertSet('value', 2)
        ->call('choose', 2)->assertSet('value', null);
});

/*
 * Color Picker and Rating.
 */

it('renders a color picker as a popover trigger with a swatch', function () {
    $html = (string) $this->blade('<x-ui::color-picker wire:model="color" value="#2563EB" placeholder="Pick" name="color" class="w-48" :swatches="[\'#2563eb\', \'#ef4444\']" />');

    expect($html)
        ->toContainMarkup('<eso-color-picker data-slot="color-picker" data-value="#2563eb" data-name="color" data-placeholder="Pick">')
        ->toContain('class="w-48" data-slot="button" data-variant="outline"')
        ->toContain('data-color-picker-trigger="data-color-picker-trigger" data-empty="false"')
        ->toContainMarkup('<span data-slot="color-picker-swatch" style="--color-picker-color: #2563eb"></span>')
        ->toContainMarkup('<span data-slot="color-picker-value">#2563eb</span>')
        ->toContainMarkup('<div data-slot="color-picker-area" role="slider" tabindex="0" aria-label="Saturation and brightness">')
        ->toContainMarkup('<input type="range" data-slot="slider-thumb" min="0" max="360" step="1" value="0" aria-orientation="horizontal" aria-label="Hue">')
        ->toContainMarkup('<input data-color-picker-input="data-color-picker-input" value="#2563eb">')
        ->toContainMarkup('<button data-color-picker-swatch="#2563eb" style="--color-picker-color: #2563eb" aria-label="#2563eb" aria-pressed="true">')
        ->toContain('aria-pressed="false"')
        ->toContainMarkup('<input type="hidden" name="color" value="#2563eb">');
});

it('expands short hex colors and ignores anything else', function () {
    $this->blade('<x-ui::color-picker value="f80" />')->assertSee('data-value="#ff8800"', false);

    $this->blade('<x-ui::color-picker value="red" placeholder="Pick" />')
        ->assertDontSee('data-value', false)
        ->assertSee('data-empty="true"', false)
        ->assertSeeMarkup('<span data-slot="color-picker-value">Pick</span>');
});

it('validates a color picker inside a field', function () {
    $this->withViewErrors(['color' => 'Pick a color.'])
        ->blade('<x-ui::field><x-ui::color-picker id="color" wire:model="color" /></x-ui::field>')
        ->assertSee('aria-invalid="true" aria-describedby="color-error"', false)
        ->assertSeeMarkupInOrder(['</eso-color-picker>', 'Pick a color.']);
});

it('renders a rating as a radio group of stars', function () {
    $html = (string) $this->blade('<x-ui::rating wire:model="score" :value="3" :max="4" name="score" />');

    expect($html)
        ->toContainMarkup('<eso-rating role="radiogroup" data-slot="rating" data-size="default" data-value="3" data-name="score">')
        ->toContainMarkup('<button type="button" role="radio" data-slot="rating-item" data-value="3" aria-checked="true" aria-label="3 of 4 stars" tabindex="0" style="--rating-fill: 100%">')
        ->toContain('data-value="4" aria-checked="false" aria-label="4 of 4 stars" tabindex="-1" style="--rating-fill: 0%"')
        ->toContainMarkup('<input type="hidden" name="score" value="3">')
        ->and(substr_count($html, 'role="radio"'))->toBe(4);
});

it('lets the first star take focus when nothing is rated, and disables the stars', function () {
    $html = (string) $this->blade('<x-ui::rating disabled />');

    expect($html)
        ->toContain('data-disabled')
        ->toContain('data-value="1" aria-checked="false" aria-label="1 of 5 stars" tabindex="0" style="--rating-fill: 0%" disabled');
});

it('shows fractions when readonly', function () {
    $html = (string) $this->blade('<x-ui::rating readonly :value="3.5" size="sm" />');

    expect($html)
        ->toContainMarkup('<span role="img" data-slot="rating" data-size="sm" data-readonly="data-readonly" aria-label="3.5 of 5 stars">')
        ->toContain('style="--rating-fill: 100%"')
        ->toContain('style="--rating-fill: 50%"')
        ->not->toContain('<button');
});
