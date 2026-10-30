@blaze(fold: true)

@props([
    'side' => 'top',
    'align' => 'center',
    'sideOffset' => 4,
    'alignOffset' => 0,
])

<div {{ $attributes->merge([
    'popover' => 'manual',
    'role' => 'tooltip',
    'data-slot' => 'tooltip-content',
    'data-side' => $side,
    'data-align' => $align,
    'style' => "--side-offset: {$sideOffset}px; --align-offset: {$alignOffset}px;",
]) }}>{{ $slot }}</div>
