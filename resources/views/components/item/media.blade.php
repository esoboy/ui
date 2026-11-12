@blaze(fold: true)

@props([
    'variant' => 'default',
])

<div {{ $attributes->merge(['data-slot' => 'item-media', 'data-variant' => $variant]) }}>{{ $slot }}</div>
