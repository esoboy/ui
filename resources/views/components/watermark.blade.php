@blaze

@use('Esoboy\Ui\Support\Watermark')

{{-- Not in shadcn (Ant Design's Watermark): text repeated over what it wraps, in the foreground color at low opacity; set --watermark-color to change it. A script puts the layer back when it is removed or changed. --}}
@props([
    'content' => null,
    'rotate' => -22,
    'gap' => [100, 100],
    'fontSize' => 16,
])

@php
$lines = array_values(array_filter(array_map('strval', (array) $content), fn (string $line) => $line !== ''));
[$gapX, $gapY] = array_pad(array_values((array) $gap), 2, ((array) $gap)[0] ?? 100);
$tile = $lines ? Watermark::tile($lines, (int) $fontSize, (int) $rotate, [(int) $gapX, (int) $gapY]) : null;
@endphp

<eso-watermark {{ $attributes->merge(['data-slot' => 'watermark']) }}>
    {{ $slot }}
    @if ($tile)
    <div data-slot="watermark-layer" aria-hidden="true" style="--watermark-image: {{ $tile['image'] }}; --watermark-size: {{ $tile['width'] }}px {{ $tile['height'] }}px"></div>
    @endif
</eso-watermark>
<x-ui::extras />
