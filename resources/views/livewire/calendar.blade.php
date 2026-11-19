<div>
    @include('ui::livewire.partials.calendar', ['calendarAttributes' => $attributes->whereDoesntStartWith('wire:model')->except('name')])
    @include('ui::livewire.partials.selection-input')
</div>
