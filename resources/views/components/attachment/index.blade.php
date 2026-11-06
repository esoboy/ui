@blaze(fold: true)

@props([
    'state' => 'done',
    'size' => 'default',
    'orientation' => 'horizontal',
])

<div {{ $attributes->merge(['data-slot' => 'attachment', 'data-state' => $state, 'data-size' => $size, 'data-orientation' => $orientation]) }}>{{ $slot }}</div>
