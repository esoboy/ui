@blaze

@aware([
    'items' => [],
    'defaultItem' => null,
])

@props([
    'name',
    'required' => false,
    'multiple' => false,
    'disabled' => false,
])

@php
$names = array_map(fn ($item) => is_array($item) ? $item['name'] : $item, $items);
$active = ! $disabled && $name === ($defaultItem ?? $names[0] ?? null);
@endphp

<fieldset {{ $attributes->merge([
    'data-slot' => 'questionnaire-item',
    'data-name' => $name,
    'data-required' => (bool) $required,
    'data-multiple' => (bool) $multiple,
    'data-active' => $active,
    'disabled' => (bool) $disabled,
    'hidden' => ! $active,
    'inert' => ! $active,
    'tabindex' => '-1',
]) }}>{{ $slot }}</fieldset>
