@blaze(fold: true)

@props([
    'variant' => 'default',
])

<div {{ $attributes->merge(['role' => 'alert', 'data-slot' => 'alert', 'data-variant' => $variant]) }}>{{ $slot }}</div>
