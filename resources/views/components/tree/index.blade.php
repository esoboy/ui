@props(['value', 'initialValue', 'nodes' => [], 'multiple' => false, 'selectParents' => false, 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'tree', get_defined_vars()))
<div {{ $attributes->only('class')->class('ui-tree ui-tree-field') }}>
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
        <div class="ui-tree-footer">{{ $footer }}</div>
    @endisset
</div>
