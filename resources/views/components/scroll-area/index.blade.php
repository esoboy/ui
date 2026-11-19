@props(['orientation' => 'vertical'])
@php
    if (!in_array($orientation, ['vertical', 'horizontal'], true)) throw new \InvalidArgumentException(__('ui::errors.scroll_orientation'));
@endphp
<div {{ $attributes->except(['tabindex', 'aria-label'])->merge(['data-slot' => 'scroll-area', 'data-orientation' => $orientation]) }} x-data="uiScrollArea" x-bind:data-overflow="overflow || null">
    <div data-slot="scroll-area-viewport" x-on:scroll.passive="measure()" {{ $attributes->only(['tabindex', 'aria-label'])->merge(['tabindex' => 0]) }}>
        <div data-slot="scroll-area-content">{{ $slot }}</div>
    </div>
    <div data-slot="scroll-area-scrollbar" data-orientation="{{ $orientation }}" aria-hidden="true" x-on:pointerdown.prevent="start($event)" x-on:pointermove="move($event)">
        <div data-slot="scroll-area-thumb" x-bind:style="vertical ? { height: thumb + '%', transform: 'translateY(' + offset + '%)' } : { width: thumb + '%', transform: 'translateX(' + offset + '%)' }"></div>
    </div>
</div>
