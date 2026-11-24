@props(['side' => 'bottom', 'align' => 'start', 'sideOffset' => 4, 'alignOffset' => 0])
<div {{ $attributes->class('ui-dropdown-menu-content ui-menu-popup ui-popup ui-list-popup')->merge(['role' => 'menu']) }}
    data-side-offset="{{ $sideOffset }}" data-align-offset="{{ $alignOffset }}" data-ui-panel data-side="{{ $side }}" data-align="{{ $align }}" popover="auto" x-bind:id="$id('ui-popover')" x-on:keydown="navigate($event)"
    x-on:toggle="toggled($event)">
    <x-ui::scroll-area class="ui-list-scroll" tabindex="-1">{{ $slot }}</x-ui::scroll-area>
</div>
