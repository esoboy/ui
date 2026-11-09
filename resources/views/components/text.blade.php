@blaze(fold: true)

@props([
    'variant' => 'primary',
    'size' => 'default',
    'color' => 'default',
])

<span {{ $attributes->merge(['data-slot' => 'text', 'data-variant' => $variant, 'data-size' => $size, 'data-color' => $color]) }}>{{ $slot }}</span>
