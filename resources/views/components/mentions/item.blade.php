@blaze

{{-- value is what is inserted after the prefix; prefix keeps the item to one prefix when there are several. --}}
@props([
    'value',
    'prefix' => null,
])

{{-- wire:key: its script gives it an id, and Livewire's morph would otherwise match it by that id and replace it. --}}
<div {{ $attributes->merge([
    'role' => 'option',
    'wire:key' => "mentions-item-{$value}",
    'data-slot' => 'mentions-item',
    'data-value' => $value,
    'data-prefix' => $prefix ?? false,
    'aria-selected' => 'false',
    'aria-disabled' => $attributes->has('disabled') ? 'true' : false,
]) }}>{{ $slot }}</div>
