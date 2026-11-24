@props([
    'comboboxField',
    'size' => 'default',
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
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), $comboboxField, get_defined_vars()))
<div {{ $attributes->only('class')->class(['ui-'.$comboboxField, 'ui-'.$comboboxField.'-field'])->merge(['data-size' => $size]) }}>
    {!! \Livewire\Livewire::mount(
        'ui::'.$comboboxField,
        [
            ...$binding->parameters,
            'size' => $size,
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
        <div class="ui-{{ $comboboxField }}-footer">{{ $footer }}</div>
    @endisset
</div>
