@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\PendingControl')

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

$value = $type === 'file' ? null : \Esoboy\Ui\Support\Model::value($attributes, $attributes->get('value'));
PendingControl::put($state, $value, $attributes->get('maxlength'));
@endphp

<input {{ $attributes->merge(['type' => $type, 'value' => $value, 'data-slot' => 'input-group-control', ...$state->attributes()]) }}>
