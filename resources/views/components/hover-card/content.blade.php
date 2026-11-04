@blaze(fold: true)

@props([
    'side' => 'bottom',
    'align' => 'center',
    'sideOffset' => 4,
    'alignOffset' => 4,
])

<div {{ $attributes->merge([
    'popover' => 'manual',
    'data-slot' => 'hover-card-content',
    'data-side' => $side,
    'data-align' => $align,
    'style' => "--side-offset: {$sideOffset}px; --align-offset: {$alignOffset}px;",
]) }}>{{ $slot }}</div>
