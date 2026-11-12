@blaze

@aware([
    'multiple' => false,
    'variant' => 'default',
    'size' => 'default',
    'spacing' => 2,
    'uiValue' => null,
    'uiModel' => null,
    'uiName' => null,
])

@props([
    'value',
])

@php
$attributes = \Esoboy\Ui\Support\Theme::control($attributes);
$binding = \Esoboy\Ui\Support\OverlayModel::attributes($uiModel) ?? new \Illuminate\View\ComponentAttributeBag;
$selected = \Esoboy\Ui\Support\Model::inherited($uiModel, $uiValue);
$checked = $multiple
    ? in_array((string) $value, array_map('strval', (array) $selected), true)
    : $selected !== null && (string) $selected === (string) $value;
@endphp

{{-- A native radio (single) or checkbox (multiple) inside the group; class styles the item. --}}
<label {{ $attributes->only(['class', 'style'])->merge(['data-slot' => 'toggle-group-item', 'data-variant' => $variant, 'data-size' => $size, 'data-spacing' => $spacing]) }}>
    <input {{ $attributes->except(['class', 'style', 'variant', 'size'])->merge(['type' => $multiple ? 'checkbox' : 'radio', 'name' => $multiple && $uiName && ! str_ends_with($uiName, '[]') ? $uiName.'[]' : $uiName, 'value' => $value, 'checked' => $checked, ...$binding->getAttributes()]) }}>
    {{ $slot }}
</label>
