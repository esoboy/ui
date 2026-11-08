@blaze(fold: true)

@aware(['uiMenubarId' => null])

@props([
    'side' => 'bottom',
    'align' => 'start',
    'sideOffset' => 8,
    'alignOffset' => -4,
])

<div {{ $attributes->merge(['id' => $uiMenubarId, 'x-data' => 'uiOverlayContent('.json_encode(null).')', 'x-on:toggle' => 'toggled($event)', 'x-on:pointerover' => 'enter()', 'x-on:pointerout' => 'leave($event)', 'x-on:pointermove' => 'hover($event)', 'x-on:click' => 'click($event)', 'x-on:keydown' => 'key($event)', 'x-on:keydown.escape.window' => 'escape()',
    'popover' => 'auto',
    'role' => 'menu',
    'tabindex' => '-1',
    'data-slot' => 'menubar-content',
    'data-side' => $side,
    'data-align' => $align,
    'style' => "--side-offset: {$sideOffset}px; --align-offset: {$alignOffset}px;",
]) }}>{{ $slot }}</div>
