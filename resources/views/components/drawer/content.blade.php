@props(['side' => 'bottom'])
@php
    if (!in_array($side, ['top', 'right', 'bottom', 'left'], true)) throw new \InvalidArgumentException(__('ui::errors.panel_side'));
@endphp
<dialog {{ $attributes->class('group/drawer-popup')->merge(['data-slot' => 'drawer-content', 'data-side' => $side, 'data-swipe-axis' => in_array($side, ['top', 'bottom']) ? 'y' : 'x', 'data-swipe-direction' => ['top' => 'up', 'bottom' => 'down', 'left' => 'left', 'right' => 'right'][$side], 'role' => 'dialog', 'tabindex' => '-1']) }}
    x-bind:id="$id('ui-dialog')" data-ui-panel x-on:click="backdrop($event)" x-on:close="closed()" x-on:cancel.prevent="close()" x-on:pointerdown="startDrag($event)" x-on:pointermove="dragging($event)" x-on:pointerup="endDrag($event)" x-on:pointercancel="endDrag($event)" x-on:touchstart.passive="touchStart($event)" x-on:touchmove="touchMove($event)" x-on:touchend="endDrag($event)" x-on:touchcancel="endDrag($event)"><div data-slot="drawer-handle" aria-hidden="true"></div><div data-slot="drawer-body">{{ $slot }}</div>
</dialog>
