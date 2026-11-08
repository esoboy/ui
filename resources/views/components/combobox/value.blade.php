@blaze(fold: true)

@props([
    'placeholder' => null,
    'label' => null,
])

<span {{ $attributes->merge(['data-slot' => 'combobox-value', 'data-placeholder' => $placeholder ?? false]) }}>{{ $label ?? ($slot->isEmpty() ? $placeholder : $slot) }}</span>
