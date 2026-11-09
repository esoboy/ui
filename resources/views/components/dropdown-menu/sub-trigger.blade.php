@blaze(fold: true)

@aware(['uiSubmenuId' => null])

@props([
    'inset' => false,
])

<button {{ $attributes->merge(['data-ui-dropdown-menu-sub' => $uiSubmenuId, 'x-data' => 'uiOverlayTrigger', 'x-on:pointerover' => 'enter($event)', 'x-on:click' => 'click($event)',
    'type' => 'button',
    'role' => 'menuitem',
    'aria-haspopup' => 'menu',
    'data-slot' => 'dropdown-menu-sub-trigger',
    'data-inset' => $inset,
    'command' => 'toggle-popover',
    'commandfor' => $uiSubmenuId,
]) }}>
    {{ $slot }}
    <x-ui::icon name="chevron-right" data-slot="dropdown-menu-sub-trigger-icon" />
</button>
