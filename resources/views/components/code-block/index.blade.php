@blaze(fold: true)

@props([
    'variant' => 'default',
    'size' => 'default',
])

<div {{ $attributes->merge(['data-slot' => 'code-block', 'data-variant' => $variant, 'data-size' => $size]) }}>{{ $slot }}</div>
