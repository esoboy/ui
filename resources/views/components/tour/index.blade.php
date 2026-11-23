@props(['initialOpen' => false])
<div {{ $attributes->class('ui-tour') }} data-ui-overlay x-data="uiTour(@js($initialOpen))" x-modelable="opened" x-id="['ui-tour']"
    x-on:resize.window="position()" x-on:scroll.window.throttle.50ms="position()">{{ $slot }}</div>
