@blaze(fold: true, unsafe: ['aria-label'])

@use('Esoboy\Ui\Support\Icons')

{{-- A Lucide icon as lucide-react renders it; a static name folds to plain SVG at compile time. Decorative unless labelled. --}}
@props([
    'name',
    'size' => null,
])

@php
    // Omitted sizes leave control-specific SVG sizing in charge.
    $sizeClass = match ($size) {
        'xs' => 'size-3',
        'sm' => 'size-3.5',
        'default' => 'size-4',
        'lg' => 'size-5',
        default => null,
    };
@endphp

<svg {{ $attributes->merge([
    'data-slot' => 'icon',
    'data-size' => $size ?? 'default',
    'xmlns' => 'http://www.w3.org/2000/svg',
    'width' => '24',
    'height' => '24',
    'viewBox' => '0 0 24 24',
    'fill' => 'none',
    'stroke' => 'currentColor',
    'stroke-width' => '2',
    'stroke-linecap' => 'round',
    'stroke-linejoin' => 'round',
    'role' => $attributes->has('aria-label') ? 'img' : false,
    'aria-hidden' => $attributes->has('aria-label') ? false : 'true',
])->class($sizeClass) }}>{!! Icons::markup($name) !!}</svg>
