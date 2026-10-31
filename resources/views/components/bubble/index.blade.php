@blaze(fold: true)

@props([
    'variant' => 'default',
    'align' => 'start',
])

<div {{ $attributes->merge(['data-slot' => 'bubble', 'data-variant' => $variant, 'data-align' => $align]) }}>{{ $slot }}</div>
