@blaze

@aware([
    'defaultValue' => null,
])

@props([
    'value',
])

<input {{ $attributes->merge([
    'type' => 'radio',
    'data-slot' => 'radio-group-item',
    'value' => $value,
    'checked' => $defaultValue !== null && (string) $defaultValue === (string) $value,
]) }}>
