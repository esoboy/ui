@blaze(fold: true)

@props([
    'size' => 'default',
])

<span {{ $attributes->merge(['data-slot' => 'avatar', 'data-size' => $size]) }}>{{ $slot }}</span>
