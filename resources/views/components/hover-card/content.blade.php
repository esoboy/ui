@blaze(fold: true)

@aware(['uiHoverId' => null])

@props([
    'side' => 'bottom',
    'align' => 'center',
    'sideOffset' => 4,
    'alignOffset' => 4,
])

<div {{ $attributes->merge(['id' => $uiHoverId, 'x-data' => 'uiOverlayContent('.json_encode(null).')', 'x-on:toggle' => 'toggled($event)', 'x-on:pointerover' => 'enter()', 'x-on:pointerout' => 'leave($event)', 'x-on:pointermove' => 'hover($event)', 'x-on:click' => 'click($event)', 'x-on:keydown' => 'key($event)', 'x-on:keydown.escape.window' => 'escape()',
    'popover' => 'manual',
    'data-slot' => 'hover-card-content',
    'data-side' => $side,
    'data-align' => $align,
    'style' => "--side-offset: {$sideOffset}px; --align-offset: {$alignOffset}px;",
]) }}>{{ $slot }}</div>
