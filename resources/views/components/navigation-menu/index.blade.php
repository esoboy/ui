@props(['side' => 'bottom', 'align' => 'start', 'sideOffset' => 8, 'alignOffset' => 0, 'openDelay' => 150, 'closeDelay' => 150])
<nav {{ $attributes->class('ui-navigation-menu group/navigation-menu') }} data-ui-overlay x-data="uiNavigationMenu(@js($openDelay), @js($closeDelay))" x-id="['ui-navigation']" x-on:keydown="key($event)" x-on:pointerleave="leave()" x-on:pointerenter="stay()">
    {{ $slot }}
    <div class="ui-navigation-menu-positioner" data-ui-panel role="dialog" popover="auto" x-bind:id="$id('ui-navigation')" data-side="{{ $side }}" data-align="{{ $align }}" data-side-offset="{{ $sideOffset }}" data-align-offset="{{ $alignOffset }}" x-on:toggle="toggled($event)" x-on:keydown="navigate($event)">
        <div class="ui-navigation-menu-popup"><div class="ui-navigation-menu-viewport">{{ $content ?? '' }}</div></div>
    </div>
</nav>
