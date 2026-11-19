@props([
    'value',
    'initialValue',
    'options' => [],
    'multiple' => false,
    'placeholder' => '',
    'size' => 'default',
    'displayValue' => null,
    'side' => 'bottom',
    'align' => 'start',
    'sideOffset' => 4,
    'alignOffset' => 0,
    'alignItemWithTrigger' => false,
    'popupClass' => '',
    'emptyTitle' => null,
    'emptyDescription' => null,
])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, 'select', get_defined_vars()))
<div data-slot="select-field">
    {!! \Livewire\Livewire::mount(
        'ui::select',
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
            'size' => $size,
            'displayValue' => $displayValue,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
        ],
        $binding->key,
    ) !!}
    {{ $slot }}
    @isset($footer)
        <div data-slot="select-footer">{{ $footer }}</div>
    @endisset
</div>
