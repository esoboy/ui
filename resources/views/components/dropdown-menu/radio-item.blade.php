@blaze

@props([
    'value',
])

{{-- A native radio inside the group; class styles the row. --}}
<label {{ $attributes->only(['class', 'style'])->merge(['data-slot' => 'dropdown-menu-radio-item']) }}>
    <input {{ $attributes->except(['class', 'style'])->merge(['type' => 'radio', 'role' => 'menuitemradio', 'value' => $value]) }}>
    <span data-slot="dropdown-menu-item-indicator">
        <x-ui::icon name="check" />
    </span>
    {{ $slot }}
</label>
