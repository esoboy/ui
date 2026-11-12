@blaze

@aware([
    'uiModel' => null,
    'uiValue' => null,
    'uiName' => null,
])

@props([
    'value',
])

@php
$binding = \Esoboy\Ui\Support\OverlayModel::attributes($uiModel) ?? new \Illuminate\View\ComponentAttributeBag;
$selected = \Esoboy\Ui\Support\Model::inherited($uiModel, $uiValue);
@endphp

<input {{ $attributes->merge([
    'type' => 'radio',
    'data-slot' => 'radio-group-item',
    'value' => $value,
    'name' => $uiName ?? 'ui-radio-'.(\Esoboy\Ui\Support\Model::key($binding) ?? 'choice'),
    'checked' => $selected !== null && (string) $selected === (string) $value,
    ...$binding->getAttributes(),
]) }}>
