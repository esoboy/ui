@blaze

{{-- A native checkbox, so wire:model binds as usual; class styles the row. --}}
<label {{ $attributes->only(['class', 'style'])->merge(['data-slot' => 'dropdown-menu-checkbox-item']) }}>
    <input {{ $attributes->except(['class', 'style'])->merge(['type' => 'checkbox', 'role' => 'menuitemcheckbox']) }}>
    <span data-slot="dropdown-menu-item-indicator">
        <x-ui::icon name="check" />
    </span>
    {{ $slot }}
</label>
