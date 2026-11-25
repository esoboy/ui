@props(['size' => 'default', 'value', 'initialValue', 'options' => [], 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'mentions', get_defined_vars()))
<{{ $binding->containerTag }} {{ $binding->loading }} {{ $attributes->only(['class', 'wire:key'])->class('ui-mentions ui-mentions-field')->merge(['data-size' => $size]) }}>
    @livewire(
        'ui::mentions',
        [
            ...$binding->parameters,
            'size' => $size,
            'options' => $options,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
        ],
        key($binding->key))
    {{ $slot }}
</{{ $binding->containerTag }}>
