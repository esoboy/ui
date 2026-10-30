@blaze

@props([
    'value',
])

{{-- wire:key: its script gives it an id, and Livewire's morph would otherwise match it by that id and replace it. --}}
<div {{ $attributes->merge([
    'role' => 'option',
    'wire:key' => "combobox-item-{$value}",
    'data-slot' => 'combobox-item',
    'data-value' => $value,
    'aria-selected' => 'false',
    'aria-disabled' => $attributes->has('disabled') ? 'true' : false,
]) }}>
    {{ $slot }}
    <span data-slot="combobox-item-indicator"><x-ui::icon name="check" /></span>
</div>
