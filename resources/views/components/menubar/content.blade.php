@props(['side' => 'bottom', 'align' => 'start', 'sideOffset' => 8, 'alignOffset' => -4])
<div {{ $attributes->class('ui-menubar-content ui-menu-popup ui-popup ui-list-popup')->merge(['role' => 'menu']) }} data-ui-panel data-side="{{ $side }}" data-align="{{ $align }}"
    data-side-offset="{{ $sideOffset }}" data-align-offset="{{ $alignOffset }}" popover="auto" @unless($attributes->has('id')) x-bind:id="$id('ui-popover')" @endunless x-on:keydown="navigate($event)"
    x-on:toggle="toggled($event)">
    <x-ui::scroll-area class="ui-list-scroll" tabindex="-1">{{ $slot }}</x-ui::scroll-area>
</div>
