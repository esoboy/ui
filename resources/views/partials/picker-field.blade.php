@php
    $binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), $picker, get_defined_vars());
    $identity = $binding->key ?? (string) \Illuminate\Support\Str::uuid();
    $owner = \Livewire\Livewire::current();
    $target = 'ui-picker-'.hash('xxh128', ($owner ? $owner->getId() : '').'|'.$identity);
    [$prompt, $popupClass] = match ($picker) {
        'date-picker' => ['select_date', 'ui-date-popup'],
        'time-picker' => ['select_time', 'ui-list-popup ui-time-popup'],
        'datetime-picker' => ['select_datetime', 'ui-list-popup ui-date-popup'],
    };
@endphp
<div class="ui-{{ $picker }} ui-{{ $picker }}-field {{ $attributes->get('class') }}">
    <div class="ui-picker-overlay" data-ui-overlay x-data="uiPopover">
        {!! \Livewire\Livewire::mount($picker === 'time-picker' ? 'ui::time-picker' : 'ui::date-picker', [
            ...$binding->parameters, ...$options, 'contentTarget' => $target,
        ], $binding->key) !!}
        <x-ui::popover.content align="start" class="ui-picker-popup ui-{{ $picker }}-panel {{ $popupClass }}" :aria-label="__('ui::ui.'.$prompt)">
            {{-- The control updates its panel here. The caller owns the adjacent slot. --}}
            @if ($picker === 'datetime-picker')
                <x-ui::scroll-area class="ui-list-scroll ui-datetime-body" tabindex="-1">
                    <div id="{{ $target }}" wire:ignore x-on:keydown.stop></div>
                </x-ui::scroll-area>
            @else
                <div id="{{ $target }}" class="ui-picker-target" wire:ignore x-on:keydown.stop></div>
            @endif
            @if (isset($extra) && $extra->hasActualContent())
                <div x-on:keydown.stop {{ $extra->attributes->class('ui-picker-extra') }}>{{ $extra }}</div>
            @endif
        </x-ui::popover.content>
    </div>
    {{ $slot }}
    @if ($picker !== 'time-picker' && isset($footer))
        <div class="ui-{{ $picker }}-footer">{{ $footer }}</div>
    @endif
</div>
