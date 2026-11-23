@props(['color' => null, 'value', 'initialValue', 'multiple' => false, 'options' => [], 'placeholder' => '', 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, 'command', get_defined_vars()))
<div data-esoboy-ui data-slot="command-field">
    {!! \Livewire\Livewire::mount(
        'ui::command',
        [
            ...$binding->parameters,
            'color' => $color,
            'multiple' => $multiple,
            'options' => $options,
            'placeholder' => $placeholder,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
        ],
        $binding->key,
    ) !!}
    {{ $slot }}
    @isset($footer)
        <div data-esoboy-ui data-slot="command-footer">{{ $footer }}</div>
    @endisset
</div>
