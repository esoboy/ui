<div {{ $attributes->whereDoesntStartWith('wire:model')->class('ui-collapsible') }} data-ui-disclosure @if($this->current) data-panel-open @endif x-data="uiDisclosure(() => {{ $this->stateExpression() }})" x-id="['ui-collapsible']" x-bind:data-panel-open="opened ? '' : null">@include('ui::livewire.partials.model-field')
    {{ $slot }}</div>
