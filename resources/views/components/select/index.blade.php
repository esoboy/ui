@props([
    'value',
    'initialValue',
    'options' => [],
    'multiple' => false,
    'required' => false,
    'placeholder' => '',
    'size' => null,
    'displayValue' => null,
    'side' => 'bottom',
    'align' => 'start',
    'sideOffset' => 4,
    'alignOffset' => 0,
    'classic' => false,
    'popupClass' => '',
    'emptyTitle' => null,
    'emptyDescription' => null,
])
@php($size ??= \Esoboy\Ui\View\ButtonGroup::defaultSize())
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'select', get_defined_vars()))
<{{ $binding->containerTag }} {{ $binding->loading }} {{ $attributes->only(['class', 'wire:key'])->class('ui-select ui-select-field') }}>
    @livewire(
        'ui::select',
        [
            ...$binding->parameters,
            'options' => $options,
            'side' => $side,
            'align' => $align,
            'sideOffset' => $sideOffset,
            'alignOffset' => $alignOffset,
            'classic' => $classic,
            'popupClass' => $popupClass,
            'multiple' => $multiple,
            'required' => $required,
            'placeholder' => $placeholder,
            'size' => $size,
            'displayValue' => $displayValue,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
        ],
        key($binding->key))
    {{ $slot }}
    @isset($footer)
        <div class="ui-select-footer">{{ $footer }}</div>
    @endisset
</{{ $binding->containerTag }}>
