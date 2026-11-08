@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\PendingControl')

@aware([
    'uiField' => false,
    'showError' => true,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);

$value = \Esoboy\Ui\Support\Model::value($attributes, (string) $slot);
PendingControl::put($state, $value, $attributes->get('maxlength'));
@endphp

<textarea {{ $attributes->merge(['data-slot' => 'input-group-control', ...$state->attributes()]) }}>{{ $value }}</textarea>
