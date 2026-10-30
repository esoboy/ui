@blaze

@props([
    'showRemove' => true,
])

{{-- A chosen item in Combobox Chips; ui.js fills one per item from the chips' template. --}}
<div {{ $attributes->merge(['data-slot' => 'combobox-chip']) }}>
    <span data-combobox-chip-label>{{ $slot }}</span>
    @if ($showRemove)
    <x-ui::button variant="ghost" size="icon-xs" data-combobox-chip-remove :aria-label="__('ui::common.remove')">
        <x-ui::icon name="x" />
    </x-ui::button>
    @endif
</div>
