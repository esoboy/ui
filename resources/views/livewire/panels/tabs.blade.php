<div {{ $attributes->whereDoesntStartWith('wire:model')->class('ui-tabs group/tabs')->merge(['data-orientation' => $tabsOrientation]) }} x-data="uiTabs(() => {{ $this->stateExpression() }})">
    @include('ui::livewire.partials.model-field')
    {{ $slot }}</div>
