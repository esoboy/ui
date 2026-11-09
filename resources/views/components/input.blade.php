@blaze

@use('Esoboy\Ui\Support\ControlState')

@aware([
    'uiField' => false,
    'showError' => true,
])

@props([
    'type' => 'text',
    'state' => null,
])

@php
$state ??= ControlState::for($attributes, $errors ?? null, $uiField, $showError);
@endphp

<input {{ $attributes->merge(['type' => $type, 'value' => $type === 'file' ? null : \Esoboy\Ui\Support\Model::value($attributes, $attributes->get('value')), 'data-slot' => 'input', ...$state->attributes()]) }}>
<x-ui::field.error :state="$state" />
