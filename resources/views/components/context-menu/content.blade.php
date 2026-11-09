@blaze(fold: true)

@aware(['uiContextId' => null])

{{-- Placed at the pointer by ui.js, alignOffset below it (shadcn's 4). --}}
@props([
    'alignOffset' => 4,
])

<div {{ $attributes->merge(['id' => $uiContextId, 'x-data' => 'uiOverlayContent('.json_encode(null).')', 'x-on:toggle' => 'toggled($event)', 'x-on:pointerover' => 'enter()', 'x-on:pointerout' => 'leave($event)', 'x-on:pointermove' => 'hover($event)', 'x-on:click' => 'click($event)', 'x-on:keydown' => 'key($event)', 'x-on:keydown.escape.window' => 'escape()',
    'popover' => 'auto',
    'role' => 'menu',
    'tabindex' => '-1',
    'data-slot' => 'context-menu-content',
    'data-align-offset' => $alignOffset,
]) }}>{{ $slot }}</div>
