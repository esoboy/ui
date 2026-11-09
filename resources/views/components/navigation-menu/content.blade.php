@blaze

@aware([
    'uiNavigationId' => null,
    'align' => 'start',
])

<div {{ $attributes->merge(['id' => $uiNavigationId, 'x-data' => 'uiOverlayContent('.json_encode(null).')', 'x-on:toggle' => 'toggled($event)', 'x-on:pointerover' => 'enter()', 'x-on:pointerout' => 'leave($event)', 'x-on:pointermove' => 'hover($event)', 'x-on:click' => 'click($event)', 'x-on:keydown' => 'key($event)', 'x-on:keydown.escape.window' => 'escape()',
    'popover' => 'auto',
    'data-slot' => 'navigation-menu-content',
    'data-side' => 'bottom',
    'data-align' => $align,
    'style' => '--side-offset: 8px;',
]) }}>{{ $slot }}</div>
