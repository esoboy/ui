@blaze

@use('Esoboy\Ui\Support\ControlState')
@use('Esoboy\Ui\Support\PendingControl')

@aware([
    'uiField' => false,
    'showError' => true,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);

PendingControl::put($state);
@endphp

<textarea {{ $attributes->merge(['data-slot' => 'input-group-control', ...$state->attributes()]) }}>{{ $slot }}</textarea>
