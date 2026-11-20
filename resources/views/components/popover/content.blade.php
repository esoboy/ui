@props(['side' => 'bottom', 'align' => 'center', 'sideOffset' => 4, 'alignOffset' => 0])
<div {{ $attributes->class('ui-popup')->merge(['data-esoboy-ui' => '', 'data-slot' => 'popover-content', 'role' => 'dialog']) }} data-side-offset="{{ $sideOffset }}" data-align-offset="{{ $alignOffset }}" data-ui-panel data-side="{{ $side }}" data-align="{{ $align }}"
    popover="auto" x-bind:id="$id('ui-popover')" x-on:keydown="navigate($event)"
    x-on:toggle="toggled($event)">{{ $slot }}</div>
