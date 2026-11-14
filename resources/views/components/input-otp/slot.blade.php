@blaze(fold: true)

@props([
    'index',
])

<div {{ $attributes->merge(['data-slot' => 'input-otp-slot', 'data-index' => $index]) }}></div>
