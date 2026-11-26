<div {{ $attributes->whereDoesntStartWith('wire:model')->class('ui-trace-collapsible') }} data-ui-disclosure x-data="uiDisclosure(() => {{ $this->stateExpression() }})" x-id="['ui-trace']">@include('ui::livewire.partials.model-field')
    {{ $slot }}</div>
