@blaze

@props([
    'value' => null,
    'keywords' => [],
])

{{-- Filtering matches the text, the value and any keywords; Enter or a click clicks the item, so wire:click works. --}}
{{-- wire:key: its script gives it an id, and Livewire's morph would otherwise match it by that id and replace it. --}}
<div {{ $attributes->merge([
    'role' => 'option',
    'wire:key' => $value !== null ? "command-item-{$value}" : false,
    'data-slot' => 'command-item',
    'data-value' => $value,
    'data-keywords' => $keywords ? implode(' ', (array) $keywords) : false,
    'aria-selected' => 'false',
    'aria-disabled' => $attributes->has('disabled') ? 'true' : false,
]) }}>
    {{ $slot }}
    <x-ui::icon name="check" data-slot="command-item-check" />
</div>
