<div {{ $attributes->whereDoesntStartWith('wire:model')->class('ui-tour') }} data-ui-overlay x-data="uiTour(() => {{ $this->stateExpression() }})" x-id="['ui-tour']"
    wire:{{ $stepEvent }}.window="advance($event.detail.direction)" x-on:resize.window="position()" x-on:scroll.window.throttle.50ms="position()">@include('ui::livewire.partials.model-field')
    {{ $slot }}</div>
