@props([
    'color' => null,
    'comboboxField',
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
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, $comboboxField, get_defined_vars()))
<div data-esoboy-ui data-slot="{{ $comboboxField }}-field">
    {!! \Livewire\Livewire::mount(
        'ui::'.$comboboxField,
        [
            ...$binding->parameters,
            'color' => $color,
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
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
        ],
        $binding->key,
    ) !!}
    {{ $slot }}
    @isset($footer)
        <div data-esoboy-ui data-slot="{{ $comboboxField }}-footer">{{ $footer }}</div>
    @endisset
</div>
