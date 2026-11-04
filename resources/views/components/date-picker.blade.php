@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\DatePresets')
@use('Esoboy\Ui\Support\Model')

@aware([
    'uiField' => false,
    'showError' => true,
])

@props([
    'mode' => 'single',
    'placeholder' => null,
    'defaultValue' => null,
    'defaultMonth' => null,
    'captionLayout' => 'label',
    'numberOfMonths' => null,
    'weekStartsOn' => null,
    'min' => null,
    'max' => null,
    'disabledDates' => [],
    'presets' => [],
    'time' => false,
    'step' => null,
    'defaultTime' => '00:00',
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$value = $defaultValue ?? Model::value($attributes);
$range = $mode === 'range';
$presets = DatePresets::normalize($presets, $mode);

// With time the value is "Y-m-d H:i" (H:i:s when step is under a minute): the calendar takes the dates, the footer's inputs the times.
$times = [];

if ($time) {
    $split = fn ($at) => array_pad(preg_split('/[ T]/', (string) $at, 2), 2, null);

    if ($range) {
        [$from, $times['from']] = $split($value['from'] ?? null);
        [$to, $times['to']] = $split($value['to'] ?? null);
        $value = ['from' => $from ?: null, 'to' => $to ?: null];
    } else {
        [$date, $times['from']] = $split($value);
        $value = $date ?: null;
    }

}
@endphp

{{-- shadcn's date picker examples as one control: wire:model binds on the picker, class styles the trigger. --}}
<eso-date-picker {{ $attributes->except(['class', 'style'])->merge(['data-slot' => 'date-picker', 'data-mode' => $mode, 'data-placeholder' => $placeholder, 'data-time' => (bool) $time, 'data-step' => $time ? $step : null, 'data-default-time' => $time ? $defaultTime : null, 'data-locale' => str_replace('_', '-', app()->getLocale()), ...$state->attributes()]) }}>
    <x-ui::popover>
        <x-ui::popover.trigger variant="outline" data-date-picker-trigger :attributes="$attributes->only(['class', 'style'])">
            @if ($range)
            <x-ui::icon name="calendar" data-icon="inline-start" />
            @endif
            <span data-slot="date-picker-value">{{ $placeholder }}</span>
            @unless ($range)
            <x-ui::icon name="chevron-down" data-icon="inline-end" />
            @endunless
        </x-ui::popover.trigger>
        <x-ui::popover.content align="start" data-date-picker-content>
            <x-ui::calendar
                :mode="$mode"
                :default-value="$value"
                :default-month="$defaultMonth"
                :caption-layout="$captionLayout"
                :number-of-months="$numberOfMonths ?? ($range ? 2 : 1)"
                :week-starts-on="$weekStartsOn"
                :min="$min"
                :max="$max"
                :disabled-dates="$disabledDates"
            />
            @if ($time)
            {{-- As in shadcn's Calendar with time example; wire:ignore leaves the times to the picker. --}}
            <div data-slot="date-picker-time" wire:ignore>
                <x-ui::field.group>
                    @foreach ($range ? ['from' => __('ui::date-picker.start-time'), 'to' => __('ui::date-picker.end-time')] : ['from' => __('ui::date-picker.time')] as $part => $label)
                    <x-ui::field>
                        <x-ui::field.label>{{ $label }}</x-ui::field.label>
                        <x-ui::input-group>
                            <x-ui::input-group.input type="time" :step="$step" :value="$times[$part] ?: $defaultTime" :data-date-picker-time="$part" />
                            <x-ui::input-group.addon><x-ui::icon name="clock-2" /></x-ui::input-group.addon>
                        </x-ui::input-group>
                    </x-ui::field>
                    @endforeach
                </x-ui::field.group>
            </div>
            @endif
            @if ($presets)
            {{-- As in shadcn's Calendar presets example. --}}
            <div data-slot="date-picker-presets">
                @foreach ($presets as $label => $preset)
                <x-ui::button variant="outline" size="sm" :data-date-picker-preset="json_encode($preset)">{{ $label }}</x-ui::button>
                @endforeach
            </div>
            @endif
        </x-ui::popover.content>
    </x-ui::popover>
</eso-date-picker>
<x-ui::field.error :state="$state" />
