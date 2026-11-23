@props(['value', 'initialValue', 'options' => [], 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'mentions', get_defined_vars()))
<div {{ $attributes->only('class')->class('ui-mentions ui-mentions-field') }}>
    {!! \Livewire\Livewire::mount(
        'ui::mentions',
        [
            ...$binding->parameters,
            'options' => $options,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
        ],
        $binding->key,
    ) !!}
    {{ $slot }}
</div>
