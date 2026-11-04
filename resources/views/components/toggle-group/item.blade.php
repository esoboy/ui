@blaze

@aware([
    'type' => 'single',
    'variant' => 'default',
    'size' => 'default',
    'spacing' => 2,
    'defaultValue' => null,
])

@props([
    'value',
])

@php
$checked = $type === 'multiple'
    ? in_array((string) $value, array_map('strval', (array) $defaultValue), true)
    : $defaultValue !== null && (string) $defaultValue === (string) $value;
@endphp

{{-- A native radio (single) or checkbox (multiple) inside the group; class styles the item. --}}
<label {{ $attributes->only(['class', 'style'])->merge(['data-slot' => 'toggle-group-item', 'data-variant' => $variant, 'data-size' => $size, 'data-spacing' => $spacing]) }}>
    <input {{ $attributes->except(['class', 'style', 'variant', 'size'])->merge(['type' => $type === 'multiple' ? 'checkbox' : 'radio', 'value' => $value, 'checked' => $checked]) }}>
    {{ $slot }}
</label>
