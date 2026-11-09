@blaze(fold: true)

@aware(['uiTooltipId' => null])

@props([
    'side' => 'top',
    'align' => 'center',
    'sideOffset' => 4,
    'alignOffset' => 0,
])

<div {{ $attributes->merge(['id' => $uiTooltipId, 'x-data' => 'uiOverlayContent('.json_encode(null).')', 'x-on:toggle' => 'toggled($event)', 'x-on:pointerover' => 'enter()', 'x-on:pointerout' => 'leave($event)', 'x-on:pointermove' => 'hover($event)', 'x-on:click' => 'click($event)', 'x-on:keydown' => 'key($event)', 'x-on:keydown.escape.window' => 'escape()',
    'popover' => 'manual',
    'role' => 'tooltip',
    'data-slot' => 'tooltip-content',
    'data-side' => $side,
    'data-align' => $align,
    'style' => "--side-offset: {$sideOffset}px; --align-offset: {$alignOffset}px;",
]) }}>{{ $slot }}</div>
