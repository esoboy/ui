@blaze(fold: true)

@props([
    'orientation' => 'vertical',
])

<div {{ $attributes->merge(['data-slot' => 'button-group-separator', 'data-orientation' => $orientation, 'role' => 'none']) }}></div>
