@blaze(fold: true)

@aware(['uiSubmenuId' => null])

@props([
    'sideOffset' => 0,
    'alignOffset' => -3,
])

<div {{ $attributes->merge(['id' => $uiSubmenuId, 'x-data' => 'uiOverlayContent()', 'x-on:toggle' => 'toggled($event)', 'x-on:pointermove' => 'hover($event)', 'x-on:click' => 'click($event)', 'x-on:keydown' => 'key($event)',
    'popover' => 'auto',
    'role' => 'menu',
    'tabindex' => '-1',
    'data-slot' => 'dropdown-menu-sub-content',
    'data-side' => 'right',
    'data-align' => 'start',
    'style' => "--side-offset: {$sideOffset}px; --align-offset: {$alignOffset}px;",
]) }}>{{ $slot }}</div>
