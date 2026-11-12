<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;

/*
 * Signature: a pad whose value is a data URL; the script lives in the signature.js module.
 */

it('renders an empty pad with its placeholder', function () {
    $this->blade('<x-ui::signature wire:model="signature" />')
        ->assertSeeMarkup('<eso-signature data-slot="signature" data-type="image/png">')
        ->assertSeeMarkup('<canvas data-slot="signature-canvas" role="img" aria-label="Signature" wire:ignore></canvas>')
        ->assertSeeMarkup('<span data-slot="signature-placeholder"  wire:ignore>Sign here</span>')
        ->assertSee('data-variant="ghost" data-size="icon-sm" data-signature-clear="data-signature-clear" aria-label="Clear signature"  hidden="hidden" wire:ignore="">', false)
        ->assertSeeMarkup('<input type="hidden" wire:model="signature">');
});

it('shows a signature it is given, and keeps it in a hidden input for forms', function () {
    $this->blade('<x-ui::signature name="sig" value="data:image/png;base64,AAAA" />')
        ->assertSee('data-value="data:image/png;base64,AAAA" data-name="sig"', false)
        ->assertSeeMarkup('<span data-slot="signature-placeholder"  hidden  wire:ignore>')
        ->assertSee('aria-label="Clear signature"   wire:ignore="">', false)
        ->assertSeeMarkup('<input type="hidden" name="sig" value="data:image/png;base64,AAAA">');
});

it('ignores a value that is not an image data URL', function () {
    $this->blade('<x-ui::signature value="javascript:alert(1)" />')
        ->assertDontSee('data-value', false)
        ->assertDontSee('alert', false);
});

it('takes the SVG type, its own placeholder and the disabled state', function () {
    $this->blade('<x-ui::signature type="image/svg+xml" placeholder="Initial here" disabled />')
        ->assertSee('data-type="image/svg+xml" data-disabled="data-disabled"', false)
        ->assertSeeMarkup('>Initial here</span>')
        ->assertSee('aria-label="Clear signature" disabled="disabled"', false);
});

it('validates inside a field', function () {
    $this->withViewErrors(['signature' => 'Please sign.'])
        ->blade('<x-ui::field><x-ui::signature id="signature" wire:model="signature" /></x-ui::field>')
        ->assertSeeMarkup('<eso-signature data-slot="signature" data-type="image/png" aria-invalid="true" aria-describedby="signature-error" id="signature">')
        ->assertSeeMarkupInOrder(['</eso-signature>', 'Please sign.']);
});

it('translates its labels', function () {
    app()->setLocale('zh_CN');

    $this->blade('<x-ui::signature />')
        ->assertSee('aria-label="签名"', false)
        ->assertSee('在此签名')
        ->assertSee('aria-label="清除签名"', false);
});

it('marks each engine instance for lazy Alpine initialization', function () {
    Route::get('/signatures', fn () => Blade::render(<<<'BLADE'
        <html><head></head><body><x-ui::signature /><x-ui::signature /></body></html>
        BLADE));

    $html = $this->get('/signatures')->getContent();

    expect(substr_count($html, 'data-engine="http://localhost/esoboy-ui/signature.js?id=5555cccc6666"'))->toBe(2)
        ->and($html)->not->toContain('<script type="module"');
});
