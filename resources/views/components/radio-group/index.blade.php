@blaze

@use('Esoboy\Ui\Support\ControlState')

@aware([
    'uiField' => false,
    'showError' => true,
])

@props([
    'value' => null,
    'options' => null,
    'uiModel' => null,
    'uiValue' => null,
    'uiName' => null,
])

@php
$state = ControlState::for($attributes, $errors ?? null, $uiField, $showError);
$name = $attributes->get('name') ?? $uiName ?? \Esoboy\Ui\Support\Model::scope('ui-radio', $loop ?? null);
@endphp

<div {{ $attributes->whereDoesntStartWith('wire:model')->except('name')->merge(['role' => 'radiogroup', 'data-slot' => 'radio-group', ...$state->attributes()]) }}>
    @if ($options !== null)
        @foreach (\Esoboy\Ui\Support\Options::normalize($options) as $option)
            @php
                $checked = (string) \Esoboy\Ui\Support\Model::value($attributes, $value) === $option['value'];
            @endphp
            <label><input type="radio" data-slot="radio-group-item" name="{{ $name }}" value="{{ $option['value'] }}" {{ $attributes->whereStartsWith('wire:model') }} @checked($checked) @disabled(! empty($option['disabled']))>{{ $option['label'] }}</label>
        @endforeach
    @else
        {{ $slot }}
    @endif
</div>
<x-ui::field.error :state="$state" />
