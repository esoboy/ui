@blaze(fold: true)

@aware(['uiMenubarId' => null])

<button {{ $attributes->merge(['data-ui-menubar' => $uiMenubarId, 'x-data' => 'uiOverlayTrigger', 'x-on:pointerover' => 'enter($event)', 'x-on:pointerout' => 'leave($event)', 'x-on:focusin' => 'enter($event)', 'x-on:focusout' => 'leave($event)', 'x-on:click' => 'click($event)', 'x-on:keydown' => 'key($event)', 'x-on:contextmenu' => 'context($event)',
    'type' => 'button',
    'role' => 'menuitem',
    'aria-haspopup' => 'menu',
    'data-slot' => 'menubar-trigger',
    'command' => 'toggle-popover',
    'commandfor' => $uiMenubarId,
]) }}>{{ $slot }}</button>
