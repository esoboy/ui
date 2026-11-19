@props(['value', 'initialValue', 'nodes' => [], 'multiple' => false, 'selectParents' => false, 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, 'tree', get_defined_vars()))
<div data-slot="tree-field">
    {!! \Livewire\Livewire::mount(
        'ui::tree',
        [
            ...$binding->parameters,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
            'nodes' => $nodes,
            'multiple' => $multiple,
            'selectParents' => $selectParents,
        ],
        $binding->key,
    ) !!}
    {{ $slot }}
    @isset($footer)
        <div data-slot="tree-footer">{{ $footer }}</div>
    @endisset
</div>
