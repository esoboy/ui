<div {{ $attributes->whereDoesntStartWith('wire:model')->class('ui-tour') }} data-ui-overlay x-data="uiTour" x-id="['ui-tour']"
    x-on:resize.window="position()" x-on:scroll.window.throttle.50ms="position()">{{ $slot }}</div>
