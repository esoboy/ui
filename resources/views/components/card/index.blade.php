@blaze(fold: true)

@props([
    'size' => 'default',
])

<div {{ $attributes->merge(['data-slot' => 'card', 'data-size' => $size]) }}>{{ $slot }}</div>
