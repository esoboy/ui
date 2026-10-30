@blaze

@aware([
    'defaultValue' => null,
])

@props([
    'value',
])

@php
$active = $defaultValue !== null && (string) $defaultValue === (string) $value;
@endphp

{{-- wire:key: its script gives it an id, and Livewire's morph would otherwise match it by that id and replace it. --}}
<button {{ $attributes->merge([
    'type' => 'button',
    'role' => 'tab',
    'wire:key' => "tabs-trigger-{$value}",
    'data-slot' => 'tabs-trigger',
    'data-value' => $value,
    'data-active' => $active,
    'aria-selected' => $active ? 'true' : 'false',
    'tabindex' => $active ? '0' : '-1',
]) }}>{{ $slot }}</button>
