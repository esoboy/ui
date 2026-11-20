@props(['value', 'initialValue', 'options' => [], 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, 'transfer', get_defined_vars()))
<div data-esoboy-ui data-slot="transfer-field">
    {!! \Livewire\Livewire::mount(
        'ui::transfer',
        [
            ...$binding->parameters,
            'options' => $options,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
        ],
        $binding->key,
    ) !!}
    {{ $slot }}
    @isset($footer)
        <div data-esoboy-ui data-slot="transfer-footer">{{ $footer }}</div>
    @endisset
</div>
