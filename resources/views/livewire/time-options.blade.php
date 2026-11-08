@php
    $partArguments = isset($part) ? "'{$part}', " : '';
    $seconds = \Esoboy\Ui\Support\TimeOptions::parse($time);
    $selected = $seconds !== null && $seconds === \Esoboy\Ui\Support\TimeOptions::parse($timeOptions->initial($time));
@endphp
<div data-slot="time-options"
    @if ($timeOptions->combined)
        x-data="uiTimeHourChooser(@js($part ?? null))" x-ref="timeOptions" x-bind:data-choosing-hour="choosingHour"
        x-on:toggle.window.capture="hourPopoverToggled($event)" x-on:keydown.escape="cancelHourChooser($event)"
    @else
        x-on:click="unitClicked($event)"
    @endif>
    @if ($timeOptions->combined)
        @php
            $slotsForHour = $timeOptions->slots($time, $shownHour);
            $hoursId = $this->getId().'-hours-'.($part ?? 'value');
        @endphp
        <div data-slot="time-options-caption">
            <x-ui::button variant="ghost" size="icon-sm" data-time-previous :disabled="$slotsForHour['previous'] === null" :aria-label="__('ui::time-picker.previous-hour')"
                wire:click="showTimeHour({{ $partArguments }}{{ $slotsForHour['previous'] ?? $slotsForHour['hour'] }})">
                <x-ui::icon name="chevron-left" class="rtl:rotate-180" />
            </x-ui::button>
            <x-ui::button variant="ghost" size="sm" data-time-hour-toggle x-ref="hourToggle" x-on:click="toggleHourChooser()" :aria-controls="$hoursId" aria-expanded="false" x-bind:aria-expanded="String(choosingHour)">
                <span data-time-hour-label aria-live="polite" x-bind:hidden="choosingHour" dir="ltr">{{ sprintf('%02d:00', $slotsForHour['hour']) }}<span>–{{ sprintf('%02d:59', $slotsForHour['hour']) }}</span></span>
                <span hidden x-bind:hidden="!choosingHour">{{ __('ui::time-picker.choose-hour') }}</span>
                <x-ui::icon name="chevron-down" />
            </x-ui::button>
            <x-ui::button variant="ghost" size="icon-sm" data-time-next :disabled="$slotsForHour['next'] === null" :aria-label="__('ui::time-picker.next-hour')"
                wire:click="showTimeHour({{ $partArguments }}{{ $slotsForHour['next'] ?? $slotsForHour['hour'] }})">
                <x-ui::icon name="chevron-right" class="rtl:rotate-180" />
            </x-ui::button>
        </div>
        <div x-bind:hidden="choosingHour">
            @include('ui::livewire.time-grid', [
                'unit' => 'combined',
                'label' => __('ui::date-picker.time'),
                'choices' => $slotsForHour['options'],
                'current' => $slotsForHour['current'],
            ])
        </div>
        <div id="{{ $hoursId }}" data-slot="time-options-grid" data-time-hour-grid data-columns="4" role="group" aria-label="{{ __('ui::time-picker.choose-hour') }}"
            hidden x-bind:hidden="!choosingHour" x-on:keydown="timeKey($event)" x-bind:aria-busy="String(jumpingHour)">
            @foreach ($slotsForHour['hours'] as $hour)
                <button type="button" data-time-option data-time-hour-choice="{{ $hour }}" tabindex="{{ $hour === $slotsForHour['hour'] ? 0 : -1 }}"
                    aria-pressed="{{ $hour === $slotsForHour['hour'] ? 'true' : 'false' }}" aria-label="{{ sprintf('%02d:00–%02d:59', $hour, $hour) }}"
                    x-on:click="jumpToHour({{ $hour }})">{{ sprintf('%02d:00', $hour) }}</button>
            @endforeach
        </div>
    @else
        @php $columns = $timeOptions->columns($time); @endphp
        <x-ui::tabs value="hour" ui-value="hour" :wire:key="'time-tabs-'.($part ?? 'value')">
            <x-ui::tabs.list :aria-label="__('ui::date-picker.time')">
                @foreach ($columns as $unit => $column)
                    <x-ui::tabs.trigger :value="$unit" :wire:key="'time-tab-'.($part ?? 'value').'-'.$unit">
                        <small>{{ __("ui::time-picker.{$unit}") }}</small>
                        <span>{{ sprintf('%02d', $column['current']) }}</span>
                    </x-ui::tabs.trigger>
                @endforeach
            </x-ui::tabs.list>
            @foreach ($columns as $unit => $column)
                <x-ui::tabs.content :value="$unit" :wire:key="'time-panel-'.($part ?? 'value').'-'.$unit" tabindex="-1">
                    @include('ui::livewire.time-grid', [
                        'unit' => $unit,
                        'label' => __("ui::time-picker.{$unit}"),
                        'choices' => $column['options'],
                        'current' => $column['current'],
                    ])
                </x-ui::tabs.content>
            @endforeach
        </x-ui::tabs>
    @endif
</div>
