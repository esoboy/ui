@blaze

@props([
    'level' => 2,
    'variant' => 'title',
    'size' => 'default',
    'color' => 'default',
])

@php
$tag = match ($level) {
    1, '1' => 'h1',
    3, '3' => 'h3',
    4, '4' => 'h4',
    5, '5' => 'h5',
    6, '6' => 'h6',
    default => 'h2',
};
@endphp

<{{ $tag }} {{ $attributes->merge(['data-slot' => 'heading', 'data-variant' => $variant, 'data-size' => $size, 'data-color' => $color]) }}>{{ $slot }}</{{ $tag }}>
