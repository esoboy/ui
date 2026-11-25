@props(['size' => 'default', 'value', 'initialValue', 'multiple' => false, 'options' => [], 'placeholder' => '', 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'command', get_defined_vars()))
<{{ $binding->containerTag }} {{ $binding->loading }} {{ $attributes->only(['class', 'wire:key'])->class('ui-command ui-command-field')->merge(['data-size' => $size]) }}>
    @livewire(
        'ui::command',
        [
            ...$binding->parameters,
            'size' => $size,
            'multiple' => $multiple,
            'options' => $options,
            'placeholder' => $placeholder,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
        ],
        key($binding->key))
    {{ $slot }}
    @isset($footer)
        <div class="ui-command-footer">{{ $footer }}</div>
    @endisset
</{{ $binding->containerTag }}>
