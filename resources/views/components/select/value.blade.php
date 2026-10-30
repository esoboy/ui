@blaze(fold: true)

@props([
    'placeholder' => null,
])

{{-- Shows the chosen item's text; ui.js fills it in. --}}
<span {{ $attributes->merge(['data-slot' => 'select-value', 'data-placeholder' => $placeholder]) }}>{{ $placeholder }}</span>
