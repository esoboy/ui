@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\DatePresets')
@use('Esoboy\Ui\Support\Model')

@aware([
    'uiField' => false,
    'showError' => true,
])

@props([
    'dayView' => null,
    'selectedView' => null,
    'mode' => 'single',
    'granularity' => 'day',
    'disabled' => false,
    'placeholder' => null,
    'value' => null,
    'month' => null,
    'captionLayout' => 'label',
    'numberOfMonths' => null,
    'weekStartsOn' => null,
    'min' => null,
    'max' => null,
    'disabledDates' => [],
    'presets' => [],
    'time' => false,
    'timeMode' => 'segmented',
    'step' => null,
    'timeValue' => '00:00',
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$value = Model::value($attributes, $value);
$granularity = $granularity === 'month' ? 'month' : 'day';
$time = $granularity === 'day' && $time;
$numberOfMonths ??= $mode === 'range' ? 2 : 1;
$presets = DatePresets::normalize($presets, $mode, $granularity);
$validation = $state->attributes();
$settings = compact('dayView', 'selectedView', 'mode', 'granularity', 'disabled', 'placeholder', 'month', 'captionLayout', 'numberOfMonths', 'weekStartsOn', 'min', 'max', 'disabledDates', 'presets', 'time', 'timeMode', 'step', 'timeValue', 'validation');
$key = $attributes->get('wire:key') ?? $attributes->get('id') ?? Model::scope('date-picker'.(Model::key($attributes) ?? ''), $loop ?? null);
@endphp

{!! \Esoboy\Ui\Support\ViewSlots::mount('ui.date-picker', ['settings' => $settings, 'value' => $value, ...$attributes->except('wire:key')->getAttributes()], 'ui-date-picker-'.$key, ['default' => $slot]) !!}
<x-ui::field.error :state="$state" />
