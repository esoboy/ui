@props(['side' => 'top', 'align' => 'center', 'sideOffset' => 4, 'alignOffset' => 0])
<div {{ $attributes->class('ui-tooltip-content ui-popup')->merge(['role' => 'tooltip']) }} data-side-offset="{{ $sideOffset }}" data-align-offset="{{ $alignOffset }}" data-ui-panel data-side="{{ $side }}" data-align="{{ $align }}"
    popover="auto" x-bind:id="$id('ui-popover')" x-on:keydown="navigate($event)"
    x-on:toggle="toggled($event)">{{ $slot }}<span class="ui-tooltip-arrow" aria-hidden="true"></span></div>
