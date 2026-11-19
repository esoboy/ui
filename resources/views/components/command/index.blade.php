@props(['value', 'initialValue', 'multiple' => false, 'options' => [], 'placeholder' => '', 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, 'command', get_defined_vars()))
<div data-slot="command-field">
    {!! \Livewire\Livewire::mount(
        'ui::command',
        [
            ...$binding->parameters,
            'multiple' => $multiple,
            'options' => $options,
            'placeholder' => $placeholder,
            'size' => 'default',
            'displayValue' => null,
            'side' => 'bottom',
            'align' => 'center',
            'sideOffset' => 4,
            'alignOffset' => 0,
            'alignItemWithTrigger' => false,
            'popupClass' => '',
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
        ],
        $binding->key,
    ) !!}
    {{ $slot }}
    @isset($footer)
        <div data-slot="command-footer">{{ $footer }}</div>
    @endisset
</div>
