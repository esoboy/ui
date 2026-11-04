@blaze(fold: true)

@props([
    'sideOffset' => 0,
    'alignOffset' => -3,
])

<div {{ $attributes->merge([
    'popover' => 'auto',
    'role' => 'menu',
    'tabindex' => '-1',
    'data-slot' => 'dropdown-menu-sub-content',
    'data-side' => 'right',
    'data-align' => 'start',
    'style' => "--side-offset: {$sideOffset}px; --align-offset: {$alignOffset}px;",
]) }}>{{ $slot }}</div>
