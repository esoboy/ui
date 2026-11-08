@blaze(fold: true)

@props([
    'variant' => 'default',
])

<div {{ $attributes->merge(['data-slot' => 'empty-icon', 'data-variant' => $variant]) }}>{{ $slot }}</div>
