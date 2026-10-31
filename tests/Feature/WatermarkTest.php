<?php

/*
 * Watermark: text tiled over what it wraps, as an SVG mask.
 */

use Esoboy\Ui\Support\Watermark;

/**
 * The SVG inside a rendered layer's mask image.
 */
function watermarkSvg(string $html): string
{
    preg_match('/--watermark-image: url\(&quot;data:image\/svg\+xml,([^&]+)&quot;\)/', $html, $match);

    return rawurldecode($match[1] ?? '');
}

it('wraps its content and lays the text over it', function () {
    $html = (string) $this->blade('<x-ui::watermark content="Esoboy" class="rounded-lg"><p>Report</p></x-ui::watermark>');

    expect($html)
        ->toContain('<eso-watermark data-slot="watermark" class="rounded-lg">')
        ->toMatch('/<p>Report<\/p>\s*<div data-slot="watermark-layer" aria-hidden="true" style="--watermark-image: url\(&quot;data:image\/svg\+xml,/')
        ->and(watermarkSvg($html))
        ->toContain('<g transform="translate(')
        ->toContain('rotate(-22)')
        ->toContain('font-size="16"')
        ->toContain('<tspan x="0" y="0">Esoboy</tspan>');
});

it('stacks several lines around the middle', function () {
    $svg = watermarkSvg((string) $this->blade('<x-ui::watermark :content="[\'张三\', \'2026-10-27\']" :font-size="20" :rotate="-30" />'));

    expect($svg)
        ->toContain('<tspan x="0" y="-15">张三</tspan><tspan x="0" y="15">2026-10-27</tspan>')
        ->toContain('rotate(-30)')
        ->toContain('font-size="20"');
});

it('escapes the text', function () {
    $html = (string) $this->blade('<x-ui::watermark content="<script>&quot;" />');

    // A static attribute passes &quot; through as text, which escapes to &amp;quot;.
    expect(watermarkSvg($html))->toContain('<tspan x="0" y="0">&lt;script&gt;&amp;quot;</tspan>')
        ->and($html)->not->toContain('<script>');
});

it('sizes the tile from the text and the gap, wide characters counting double', function () {
    $latin = Watermark::tile(['abcd'], 16, -22, [100, 80]);
    $wide = Watermark::tile(['张三李四'], 16, -22, [100, 80]);

    expect($latin['width'])->toBe((int) ceil(4 * 0.55 * 16 + 100))
        ->and($wide['width'])->toBe((int) ceil(8 * 0.55 * 16 + 100))
        ->and($latin['height'])->toBe((int) ceil(1.5 * 16 + 80) * 2);
});

it('takes one gap for both directions', function () {
    expect(watermarkSvg((string) $this->blade('<x-ui::watermark content="ab" :gap="40" />')))
        ->toContain('width="'.(int) ceil(2 * 0.55 * 16 + 40).'"');
});

it('draws nothing without content', function () {
    $this->blade('<x-ui::watermark><p>Report</p></x-ui::watermark>')
        ->assertDontSee('watermark-layer', false);
});
