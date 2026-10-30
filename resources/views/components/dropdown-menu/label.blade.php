@blaze(fold: true)

@props([
    'inset' => false,
])

<div {{ $attributes->merge(['data-slot' => 'dropdown-menu-label', 'data-inset' => $inset]) }}>{{ $slot }}</div>
