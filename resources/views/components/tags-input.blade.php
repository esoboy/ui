@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\Model')

@aware([
    'uiField' => false,
    'showError' => true,
])

{{-- Enter or a comma adds what was typed, Backspace in the empty input removes the last tag. --}}
@props([
    'tagView' => null,
    'placeholder' => null,
    'max' => null,
    'disabled' => false,
    'value' => null,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$value = Model::value($attributes, $value) ?? [];
$validation = $state->attributes();
$settings = compact('tagView', 'placeholder', 'max', 'disabled', 'validation');
$key = $attributes->get('wire:key') ?? $attributes->get('id') ?? Model::scope('tags-input'.(Model::key($attributes) ?? ''), $loop ?? null);
@endphp

{!! \Esoboy\Ui\Support\ViewSlots::mount('ui.tags-input', ['settings' => $settings, 'value' => $value, ...$attributes->except('wire:key')->getAttributes()], 'ui-tags-input-'.$key, ['default' => $slot]) !!}
<x-ui::field.error :state="$state" />
<x-ui::extras />
