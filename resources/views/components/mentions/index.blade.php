@props(['size' => 'default', 'value', 'initialValue', 'options' => [], 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'mentions', get_defined_vars()))
<div {{ $attributes->only('class')->class('ui-mentions ui-mentions-field')->merge(['data-size' => $size]) }}>
    {!! \Livewire\Livewire::mount(
        'ui::mentions',
        [
            ...$binding->parameters,
            'size' => $size,
            'options' => $options,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
        ],
        $binding->key,
    ) !!}
    {{ $slot }}
</div>
