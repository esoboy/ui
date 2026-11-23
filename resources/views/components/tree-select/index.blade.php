@props(['value', 'initialValue', 'nodes' => [], 'multiple' => false, 'selectParents' => false, 'placeholder' => '', 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'tree-select', get_defined_vars()))
<div {{ $attributes->only('class')->class('ui-tree-select ui-tree-select-field') }}>
    {!! \Livewire\Livewire::mount(
        'ui::tree-select',
        [
            ...$binding->parameters,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
            'nodes' => $nodes,
            'multiple' => $multiple,
            'selectParents' => $selectParents,
            'placeholder' => $placeholder,
        ],
        $binding->key,
    ) !!}
    {{ $slot }}
    @isset($footer)
        <div class="ui-tree-select-footer">{{ $footer }}</div>
    @endisset
</div>
