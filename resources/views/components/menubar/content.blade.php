@blaze(fold: true)

@props([
    'side' => 'bottom',
    'align' => 'start',
    'sideOffset' => 8,
    'alignOffset' => -4,
])

<div {{ $attributes->merge([
    'popover' => 'auto',
    'role' => 'menu',
    'tabindex' => '-1',
    'data-slot' => 'menubar-content',
    'data-side' => $side,
    'data-align' => $align,
    'style' => "--side-offset: {$sideOffset}px; --align-offset: {$alignOffset}px;",
]) }}>{{ $slot }}</div>
