@props(['side' => 'top', 'align' => 'center', 'sideOffset' => 4, 'alignOffset' => 0])
<div {{ $attributes->class('ui-popup')->merge(['data-esoboy-ui' => '', 'data-slot' => 'tooltip-content', 'role' => 'tooltip']) }} data-side-offset="{{ $sideOffset }}" data-align-offset="{{ $alignOffset }}" data-ui-panel data-side="{{ $side }}" data-align="{{ $align }}"
    popover="auto" x-bind:id="$id('ui-popover')" x-on:keydown="navigate($event)"
    x-on:toggle="toggled($event)">{{ $slot }}<span data-esoboy-ui data-slot="tooltip-arrow" aria-hidden="true"></span></div>
