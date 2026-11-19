@props([
    'value',
    'initialValue',
    'options' => [],
    'multiple' => false,
    'placeholder' => '',
    'clearable' => false,
    'showRemove' => true,
    'showTrigger' => true,
    'prefixIcon' => null,
    'searchInPopup' => false,
    'searchPlaceholder' => '',
    'autoHighlight' => false,
    'side' => 'bottom',
    'align' => 'start',
    'sideOffset' => 6,
    'alignOffset' => 0,
    'alignItemWithTrigger' => false,
    'popupClass' => '',
    'emptyTitle' => null,
    'emptyDescription' => null,
])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, 'combobox', get_defined_vars()))
<div data-slot="combobox-field">
    {!! \Livewire\Livewire::mount(
        'ui::combobox',
        [
            ...$binding->parameters,
            'options' => $options,
            'side' => $side,
            'align' => $align,
            'sideOffset' => $sideOffset,
            'alignOffset' => $alignOffset,
            'alignItemWithTrigger' => $alignItemWithTrigger,
            'popupClass' => $popupClass,
            'multiple' => $multiple,
            'placeholder' => $placeholder,
            'clearable' => $clearable,
            'showRemove' => $showRemove,
            'showTrigger' => $showTrigger,
            'prefixIcon' => $prefixIcon,
            'searchInPopup' => $searchInPopup,
            'searchPlaceholder' => $searchPlaceholder,
            'autoHighlight' => $autoHighlight,
            'size' => 'default',
            'displayValue' => null,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
        ],
        $binding->key,
    ) !!}
    {{ $slot }}
    @isset($footer)
        <div data-slot="combobox-footer">{{ $footer }}</div>
    @endisset
</div>
