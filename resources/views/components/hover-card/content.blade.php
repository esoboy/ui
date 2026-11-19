@props(['side' => 'bottom', 'align' => 'center', 'alignOffset' => 4, 'sideOffset' => 4])
<div {{ $attributes->class('ui-popup')->merge(['data-slot' => 'hover-card-content', 'role' => 'dialog']) }}
    data-side-offset="{{ $sideOffset }}" data-ui-panel data-side="{{ $side }}" data-align="{{ $align }}" data-align-offset="{{ $alignOffset }}" popover="auto" x-bind:id="$id('ui-popover')" x-on:keydown="navigate($event)"
    x-on:toggle="toggled($event)">{{ $slot }}</div>
