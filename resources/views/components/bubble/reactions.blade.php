@blaze(fold: true)

@props([
    'side' => 'bottom',
    'align' => 'end',
])

<div {{ $attributes->merge(['data-slot' => 'bubble-reactions', 'data-align' => $align, 'data-side' => $side]) }}>{{ $slot }}</div>
