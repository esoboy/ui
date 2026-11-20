@props(['value', 'initialValue', 'options' => [], 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, 'mentions', get_defined_vars()))
<div data-esoboy-ui data-slot="mentions-field">
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
