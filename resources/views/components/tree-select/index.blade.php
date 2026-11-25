@props(['size' => 'default', 'value', 'initialValue', 'nodes' => [], 'multiple' => false, 'selectParents' => false, 'placeholder' => '', 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'tree-select', get_defined_vars()))
<{{ $binding->containerTag }} {{ $binding->loading }} {{ $attributes->only(['class', 'wire:key'])->class('ui-tree-select ui-tree-select-field')->merge(['data-size' => $size]) }}>
    @livewire(
        'ui::tree-select',
        [
            ...$binding->parameters,
            'size' => $size,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
            'nodes' => $nodes,
            'multiple' => $multiple,
            'selectParents' => $selectParents,
            'placeholder' => $placeholder,
        ],
        key($binding->key))
    {{ $slot }}
    @isset($footer)
        <div class="ui-tree-select-footer">{{ $footer }}</div>
    @endisset
</{{ $binding->containerTag }}>
