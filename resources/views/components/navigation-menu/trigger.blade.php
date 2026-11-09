@blaze(fold: true)

@aware(['uiNavigationId' => null])

{{-- Opens the Navigation Menu Content after it, on hover or click (ui.js). --}}
<button {{ $attributes->merge(['data-ui-navigation-menu' => $uiNavigationId, 'x-data' => 'uiOverlayTrigger', 'x-on:pointerover' => 'enter($event)', 'x-on:pointerout' => 'leave($event)', 'x-on:focusin' => 'enter($event)', 'x-on:focusout' => 'leave($event)', 'x-on:click' => 'click($event)', 'x-on:keydown' => 'key($event)', 'x-on:contextmenu' => 'context($event)',
    'type' => 'button',
    'aria-expanded' => 'false',
    'data-slot' => 'navigation-menu-trigger',
]) }}>
    {{ $slot }}
    <x-ui::icon name="chevron-down" data-slot="navigation-menu-trigger-icon" />
</button>
