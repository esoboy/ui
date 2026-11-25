@props(['size' => 'default', 'value', 'initialValue', 'nodes' => [], 'multiple' => false, 'selectParents' => false, 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'tree', get_defined_vars()))
<{{ $binding->containerTag }} {{ $binding->loading }} {{ $attributes->only(['class', 'wire:key'])->class('ui-tree ui-tree-field')->merge(['data-size' => $size]) }}>
    @livewire(
        'ui::tree',
        [
            ...$binding->parameters,
            'size' => $size,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
            'nodes' => $nodes,
            'multiple' => $multiple,
            'selectParents' => $selectParents,
        ],
        key($binding->key))
    {{ $slot }}
    @isset($footer)
        <div class="ui-tree-footer">{{ $footer }}</div>
    @endisset
</{{ $binding->containerTag }}>
