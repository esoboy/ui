@props(['value', 'initialValue', 'multiple' => false, 'options' => [], 'placeholder' => '', 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'command', get_defined_vars()))
<div {{ $attributes->only('class')->class('ui-command ui-command-field') }}>
    {!! \Livewire\Livewire::mount(
        'ui::command',
        [
            ...$binding->parameters,
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
        <div class="ui-command-footer">{{ $footer }}</div>
    @endisset
</div>
