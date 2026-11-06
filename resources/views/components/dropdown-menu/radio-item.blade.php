@blaze

@aware(['uiModel' => null, 'uiValue' => null, 'uiName' => null])

@props([
    'value',
])

@php($attributes = \Esoboy\Ui\Support\Theme::control($attributes))

{{-- A native radio inside the group; class styles the row. --}}
<label {{ $attributes->only(['class', 'style'])->merge(['data-slot' => 'dropdown-menu-radio-item']) }}>
    <input {{ $attributes->except(['class', 'style'])->merge(['type' => 'radio', 'role' => 'menuitemradio', 'value' => $value, 'name' => $uiName, 'checked' => (string) \Esoboy\Ui\Support\Model::inherited($uiModel, $uiValue) === (string) $value, ...(\Esoboy\Ui\Support\OverlayModel::attributes($uiModel)?->getAttributes() ?? [])]) }}>
    <span data-slot="dropdown-menu-item-indicator">
        <x-ui::icon name="check" />
    </span>
    {{ $slot }}
</label>
