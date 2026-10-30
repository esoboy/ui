@blaze

@aware([
    'orientation' => 'horizontal',
])

@props([
    'variant' => 'default',
])

<div {{ $attributes->merge(['role' => 'tablist', 'data-slot' => 'tabs-list', 'data-variant' => $variant, 'aria-orientation' => $orientation]) }}>{{ $slot }}</div>
