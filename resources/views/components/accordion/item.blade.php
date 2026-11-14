@blaze

@aware([
    'uiValue' => null,
    'uiModel' => null,
])

@props([
    'value' => null,
    'disabled' => false,
])

@php
$selected = \Esoboy\Ui\Support\Model::inherited($uiModel, $uiValue);
$open = $value !== null && in_array((string) $value, array_map('strval', (array) $selected), true);
@endphp

{{-- wire:ignore.self keeps Livewire morphs from closing an item the user opened. --}}
<details {{ $attributes->merge(['data-slot' => 'accordion-item', 'data-value' => $value, 'data-disabled' => $disabled, 'open' => $open]) }} wire:ignore.self>{{ $slot }}</details>
