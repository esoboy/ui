@blaze

@use('Esoboy\Ui\Support\Model')

@props([
    'dayView' => null,
    'mode' => 'single',
    'disabled' => false,
    'value' => null,
    'month' => null,
    'captionLayout' => 'label',
    'numberOfMonths' => 1,
    'showOutsideDays' => true,
    'weekStartsOn' => null,
    'min' => null,
    'max' => null,
    'disabledDates' => [],
    'startMonth' => null,
    'endMonth' => null,
])

@php
$value = Model::value($attributes, $value);
$settings = compact('dayView', 'mode', 'disabled', 'month', 'captionLayout', 'numberOfMonths', 'showOutsideDays', 'weekStartsOn', 'min', 'max', 'disabledDates', 'startMonth', 'endMonth');
$key = $attributes->get('wire:key') ?? $attributes->get('id') ?? Model::scope('calendar'.(Model::key($attributes) ?? ''), $loop ?? null);
@endphp

{!! \Esoboy\Ui\Support\ViewSlots::mount('ui.calendar', ['settings' => $settings, 'value' => $value, ...$attributes->except('wire:key')->getAttributes()], 'ui-calendar-'.$key, ['default' => $slot, 'header' => $header ?? null]) !!}
