@blaze(fold: true)

@props([
    'size' => 'default',
    'color' => 'default',
])

<span {{ $attributes->merge(['data-slot' => 'text', 'data-size' => $size, 'data-color' => $color]) }}>{{ $slot }}</span>
