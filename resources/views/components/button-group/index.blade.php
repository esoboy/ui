@blaze(fold: true)

@props([
    'orientation' => 'horizontal',
])

<div {{ $attributes->merge(['role' => 'group', 'data-slot' => 'button-group', 'data-orientation' => $orientation]) }}>{{ $slot }}</div>
