<eso-date-picker {{ $attributes->whereDoesntStartWith('wire:model')->except(['class', 'style', 'name'])->merge(['data-slot' => 'date-picker', 'data-mode' => $settings['mode'], 'data-time' => (bool) ($settings['time'] ?? false), ...($settings['validation'] ?? [])]) }}
    @if ($settings['time'] ?? false) x-data="uiTimePicker" x-on:toggle.capture="scrollToTime($event)" @endif>
    <x-ui::popover>
        <x-ui::popover.trigger><x-ui::button variant="outline" :disabled="$settings['disabled'] ?? false" data-date-picker-trigger :data-empty="$label === '' ? 'true' : 'false'" :attributes="$attributes->only(['class', 'style'])->merge($settings['validation'] ?? [])">
            @if ($settings['mode'] === 'range')<x-ui::icon name="calendar" data-icon="inline-start" />@endif
            <span data-slot="date-picker-value">{{ $label !== '' && isset($settings['selectedView']) ? view($settings['selectedView'], ['value' => $value, 'label' => $label]) : ($label ?: ($settings['placeholder'] ?? null)) }}</span>
            @if ($settings['mode'] !== 'range')<x-ui::icon name="chevron-down" data-icon="inline-end" />@endif
        </x-ui::button></x-ui::popover.trigger>
        <x-ui::popover.content align="start" data-date-picker-content>
            <div data-slot="date-picker-body" @if (($settings['time'] ?? false) && $settings['mode'] !== 'range' && count($months) === 1) data-time-side @endif>
                <eso-calendar data-slot="calendar" data-mode="{{ $settings['mode'] }}" data-month="{{ $month }}" data-week-starts-on="{{ $settings['weekStartsOn'] ?? ((int) \IntlCalendar::createInstance(null, app()->getLocale())->getFirstDayOfWeek() - 1) }}" x-data="uiCalendar" x-on:keydown="key($event)">
                    @include('ui::livewire.calendar-grid')
                </eso-calendar>
                @if ($settings['time'] ?? false)
                    <div data-slot="date-picker-time">
                        @foreach ($settings['mode'] === 'range' ? ['from' => __('ui::date-picker.start-time'), 'to' => __('ui::date-picker.end-time')] : ['from' => __('ui::date-picker.time')] as $part => $timeLabel)
                            <fieldset data-date-picker-time="{{ $part }}">
                                <legend>{{ $timeLabel }}</legend>
                                @include('ui::livewire.time-options', ['time' => $times[$part], 'shownHour' => $shownHours[$part]])
                            </fieldset>
                        @endforeach
                    </div>
                @endif
            </div>
            @if (($settings['presets'] ?? []))
                <div data-slot="date-picker-presets">
                    @foreach (($settings['presets'] ?? []) as $presetLabel => $presetValue)
                        <x-ui::button variant="outline" size="sm" :data-date-picker-preset="json_encode($presetValue)" wire:click="preset({{ \Illuminate\Support\Js::from($presetLabel) }})">{{ $presetLabel }}</x-ui::button>
                    @endforeach
                </div>
            @endif
            @if ($slots->has('default')){{ $slots }}@endif
        </x-ui::popover.content>
    </x-ui::popover>
    @include('ui::livewire.form-value')
</eso-date-picker>
