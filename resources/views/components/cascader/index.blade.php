@props(['color' => null, 'value', 'initialValue', 'multiple' => false, 'selectParents' => false, 'nodes' => [], 'placeholder' => '', 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, 'cascader', get_defined_vars()))
<div data-esoboy-ui data-slot="cascader-field">
    {!! \Livewire\Livewire::mount(
        'ui::cascader',
        [
            ...$binding->parameters,
            'color' => $color,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
            'multiple' => $multiple,
            'selectParents' => $selectParents,
            'nodes' => $nodes,
            'placeholder' => $placeholder,
        ],
        $binding->key,
    ) !!}
    {{ $slot }}
    @isset($footer)
        <div data-esoboy-ui data-slot="cascader-footer">{{ $footer }}</div>
    @endisset
</div>
