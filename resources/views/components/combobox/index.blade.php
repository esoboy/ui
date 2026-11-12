@blaze
@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')
@aware(['uiField' => false, 'showError' => true])
@props(['options' => [], 'value' => null, 'multiple' => false, 'autocomplete' => false, 'filter' => true, 'placeholder' => null, 'clearable' => false, 'trigger' => true, 'button' => false, 'disabled' => false, 'empty' => null, 'optionView' => null, 'selectedView' => null])
@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$value = Model::value($attributes, $value);
$validation = $state->attributes();
$settings = compact('multiple', 'autocomplete', 'filter', 'placeholder', 'clearable', 'trigger', 'button', 'disabled', 'empty', 'validation', 'optionView', 'selectedView');
$key = $attributes->get('wire:key') ?? $attributes->get('id') ?? Model::scope('combobox'.(Model::key($attributes) ?? ''), $loop ?? null);
@endphp
{!! \Esoboy\Ui\Support\ViewSlots::mount('ui.combobox', ['options' => $options, 'settings' => $settings, 'value' => $value, ...$attributes->except('wire:key')->getAttributes()], 'ui-combobox-'.$key, ['default' => $slot, 'empty' => $emptyState ?? null]) !!}
<x-ui::field.error :state="$state" />
