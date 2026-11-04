@blaze(fold: true)

@props([
    'inset' => false,
])

{{-- Opens the Dropdown Menu Sub Content after it; commandfor stays empty, ui.js points it there. --}}
<button {{ $attributes->merge([
    'type' => 'button',
    'role' => 'menuitem',
    'aria-haspopup' => 'menu',
    'data-slot' => 'dropdown-menu-sub-trigger',
    'data-inset' => $inset,
    'command' => 'toggle-popover',
    'commandfor' => '',
]) }}>
    {{ $slot }}
    <x-ui::icon name="chevron-right" data-slot="dropdown-menu-sub-trigger-icon" />
</button>
