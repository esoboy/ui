@props(['side' => 'right', 'align' => 'start', 'sideOffset' => 0, 'alignOffset' => -3])
<div {{ $attributes->class('ui-dropdown-menu-sub-content ui-popup ui-list-popup')->merge(['role' => 'menu']) }} data-ui-panel data-side="{{ $side }}" data-align="{{ $align }}" data-side-offset="{{ $sideOffset }}" data-align-offset="{{ $alignOffset }}" popover="auto" x-bind:id="$id('ui-popover')" x-on:keydown="navigate($event)" x-on:toggle="toggled($event)">
    <x-ui::scroll-area class="ui-list-scroll" tabindex="-1">{{ $slot }}</x-ui::scroll-area>
</div>
