@blaze(fold: true)

@aware(['uiContextId' => null])

{{-- Right-clicking the area opens the Context Menu Content after it at the pointer. --}}
<div {{ $attributes->merge(['data-ui-context-menu' => $uiContextId, 'x-data' => 'uiOverlayTrigger', 'x-on:pointerover' => 'enter($event)', 'x-on:pointerout' => 'leave($event)', 'x-on:focusin' => 'enter($event)', 'x-on:focusout' => 'leave($event)', 'x-on:click' => 'click($event)', 'x-on:keydown' => 'key($event)', 'x-on:contextmenu' => 'context($event)', 'data-slot' => 'context-menu-trigger']) }}>{{ $slot }}</div>
