@blaze(fold: true)

@props([
    'align' => 'start',
])

<div {{ $attributes->merge(['data-slot' => 'message', 'data-align' => $align]) }}>{{ $slot }}</div>
