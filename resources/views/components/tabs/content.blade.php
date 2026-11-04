@blaze

@aware([
    'defaultValue' => null,
])

@props([
    'value',
])

{{-- wire:key: its script gives it an id, and Livewire's morph would otherwise match it by that id and replace it. --}}
<div {{ $attributes->merge([
    'role' => 'tabpanel',
    'wire:key' => "tabs-content-{$value}",
    'tabindex' => '0',
    'data-slot' => 'tabs-content',
    'data-value' => $value,
    'hidden' => $defaultValue === null || (string) $defaultValue !== (string) $value,
]) }}>{{ $slot }}</div>
