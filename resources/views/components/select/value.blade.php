@blaze(fold: true)

@props([
    'placeholder' => null,
    'label' => null,
])

<span {{ $attributes->merge(['data-slot' => 'select-value', 'data-placeholder' => $placeholder]) }}>{{ $label ?? $placeholder }}</span>
