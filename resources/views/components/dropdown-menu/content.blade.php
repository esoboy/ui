@blaze(fold: true)

@props([
    'side' => 'bottom',
    'align' => 'start',
    'sideOffset' => 4,
    'alignOffset' => 0,
])

<div {{ $attributes->merge([
    'popover' => 'auto',
    'role' => 'menu',
    'tabindex' => '-1',
    'data-slot' => 'dropdown-menu-content',
    'data-side' => $side,
    'data-align' => $align,
    'style' => "--side-offset: {$sideOffset}px; --align-offset: {$alignOffset}px;",
]) }}>{{ $slot }}</div>
