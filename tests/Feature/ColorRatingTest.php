<?php

/*
 * Color Picker and Rating.
 */

it('renders a color picker as a popover trigger with a swatch', function () {
    $html = (string) $this->blade('<x-ui::color-picker wire:model="color" default-value="#2563EB" placeholder="Pick" name="color" class="w-48" :swatches="[\'#2563eb\', \'#ef4444\']" />');

    expect($html)
        ->toContain('<eso-color-picker data-slot="color-picker" data-value="#2563eb" data-name="color" data-placeholder="Pick" wire:model="color">')
        ->toContain('class="w-48" data-slot="button" data-variant="outline"')
        ->toContain('data-color-picker-trigger="data-color-picker-trigger" data-empty="false"')
        ->toContain('<span data-slot="color-picker-swatch" style="--color-picker-color: #2563eb"></span>')
        ->toContain('<span data-slot="color-picker-value">#2563eb</span>')
        ->toContain('<div data-slot="color-picker-area" role="slider" tabindex="0" aria-label="Saturation and brightness">')
        ->toContain('<input type="range" data-slot="color-picker-hue" min="0" max="360" step="1" aria-label="Hue">')
        ->toContain('data-color-picker-input value="#2563eb"')
        ->toContain('data-color-picker-swatch="#2563eb" style="--color-picker-color: #2563eb" aria-label="#2563eb" aria-pressed="true"')
        ->toContain('aria-pressed="false"')
        ->toContain('<input type="hidden" name="color" value="#2563eb">');
});

it('expands short hex colors and ignores anything else', function () {
    $this->blade('<x-ui::color-picker default-value="f80" />')->assertSee('data-value="#ff8800"', false);

    $this->blade('<x-ui::color-picker default-value="red" placeholder="Pick" />')
        ->assertDontSee('data-value', false)
        ->assertSee('data-empty="true"', false)
        ->assertSee('<span data-slot="color-picker-value">Pick</span>', false);
});

it('validates a color picker inside a field', function () {
    $this->withViewErrors(['color' => 'Pick a color.'])
        ->blade('<x-ui::field><x-ui::color-picker id="color" wire:model="color" /></x-ui::field>')
        ->assertSee('aria-invalid="true" aria-describedby="color-error"', false)
        ->assertSeeInOrder(['</eso-color-picker>', 'Pick a color.'], false);
});

it('renders a rating as a radio group of stars', function () {
    $html = (string) $this->blade('<x-ui::rating wire:model="score" :default-value="3" :max="4" name="score" />');

    expect($html)
        ->toContain('<eso-rating role="radiogroup" data-slot="rating" data-size="default" data-value="3" data-name="score" wire:model="score">')
        ->toMatch('/<button type="button" role="radio" data-slot="rating-item" data-value="3" aria-checked="true" aria-label="3 of 4 stars" tabindex="0" style="--rating-fill: 100%"\s*>/')
        ->toContain('data-value="4" aria-checked="false" aria-label="4 of 4 stars" tabindex="-1" style="--rating-fill: 0%"')
        ->toContain('<input type="hidden" name="score" value="3">')
        ->and(substr_count($html, 'role="radio"'))->toBe(4);
});

it('lets the first star take focus when nothing is rated, and disables the stars', function () {
    $html = (string) $this->blade('<x-ui::rating disabled />');

    expect($html)
        ->toContain('data-disabled')
        ->toContain('data-value="1" aria-checked="false" aria-label="1 of 5 stars" tabindex="0" style="--rating-fill: 0%" disabled');
});

it('shows fractions when readonly', function () {
    $html = (string) $this->blade('<x-ui::rating readonly :default-value="3.5" size="sm" />');

    expect($html)
        ->toContain('<span role="img" data-slot="rating" data-size="sm" data-readonly="data-readonly" aria-label="3.5 of 5 stars">')
        ->toContain('style="--rating-fill: 100%"')
        ->toContain('style="--rating-fill: 50%"')
        ->not->toContain('<button');
});
