@blaze(fold: true)

@props([
    'placeholder' => null,
])

{{-- The chosen item's text (the items', comma-separated), else the placeholder; ui.js keeps it. --}}
<span {{ $attributes->merge(['data-slot' => 'combobox-value', 'data-placeholder' => $placeholder ?? false]) }}>{{ $placeholder }}</span>
