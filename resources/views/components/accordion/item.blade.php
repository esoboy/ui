@blaze

@aware([
    'defaultValue' => null,
])

@props([
    'value' => null,
    'disabled' => false,
])

@php
$open = $value !== null && in_array((string) $value, array_map('strval', (array) $defaultValue), true);
@endphp

{{-- wire:ignore.self keeps Livewire morphs from closing an item the user opened. --}}
<details {{ $attributes->merge(['data-slot' => 'accordion-item', 'data-value' => $value, 'data-disabled' => $disabled, 'open' => $open]) }} wire:ignore.self>{{ $slot }}</details>
