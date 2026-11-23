@props(['orientation' => 'vertical'])
@php
    if (!in_array($orientation, ['vertical', 'horizontal'], true)) throw new \InvalidArgumentException(__('ui::errors.scroll_orientation'));
@endphp
<div {{ $attributes->except(['tabindex', 'aria-label'])->merge(['class' => 'ui-scroll-area', 'data-orientation' => $orientation]) }} x-data="uiScrollArea" x-bind:data-overflow="overflow || null">
    <div x-on:scroll.passive="measure()" {{ $attributes->only(['tabindex', 'aria-label'])->merge(['tabindex' => 0])->class('ui-scroll-area-viewport') }}>
        <div class="ui-scroll-area-content">{{ $slot }}</div>
    </div>
    <div class="ui-scroll-area-scrollbar" data-orientation="{{ $orientation }}" aria-hidden="true" x-on:pointerdown.prevent="start($event)" x-on:pointermove="move($event)">
        <div class="ui-scroll-area-thumb" x-bind:style="vertical ? { height: thumb + '%', transform: 'translateY(' + offset + '%)' } : { width: thumb + '%', transform: 'translateX(' + offset + '%)' }"></div>
    </div>
</div>
