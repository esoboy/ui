<eso-time-picker data-slot="time-picker" x-data="uiTimePicker" x-on:toggle.capture="scrollToTime($event)" x-on:ui-time-picked="$el.querySelector('[data-time-picker-content]:popover-open')?.hidePopover()">
    <x-ui::popover>
        <x-ui::popover.trigger>
            <x-ui::button variant="outline" :disabled="($settings['disabled'] ?? false) || ($settings['readonly'] ?? false)" data-time-picker-trigger :data-empty="$label === '' ? 'true' : 'false'" :attributes="$attributes->whereDoesntStartWith('wire:model')->except('name')->merge($settings['validation'] ?? [])">
                <x-ui::icon name="clock" data-icon="inline-start" />
                <span data-slot="time-picker-value">{{ $label === '' ? ($settings['placeholder'] ?? __('ui::time-picker.placeholder')) : $label }}</span>
                <x-ui::icon name="chevron-down" data-icon="inline-end" />
            </x-ui::button>
        </x-ui::popover.trigger>
        <x-ui::popover.content align="start" data-time-picker-content>
            @include('ui::livewire.time-options', ['time' => $value, 'shownHour' => $shownHour])
            @if ($slots->has('default')){{ $slots }}@endif
        </x-ui::popover.content>
    </x-ui::popover>
    @include('ui::livewire.form-value')
</eso-time-picker>
