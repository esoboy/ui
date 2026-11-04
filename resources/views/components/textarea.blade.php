@blaze

@use('Esoboy\Ui\Support\ControlState')

@aware([
    'uiField' => false,
    'showError' => true,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
@endphp

<textarea {{ $attributes->merge(['data-slot' => 'textarea', ...$state->attributes()]) }}>{{ $slot }}</textarea>
<x-ui::field.error :state="$state" />
