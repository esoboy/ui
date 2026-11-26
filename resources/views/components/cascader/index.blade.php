@props(['size' => 'default', 'value', 'initialValue', 'multiple' => false, 'selectParents' => false, 'nodes' => [], 'placeholder' => '', 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'cascader', get_defined_vars()))
<{{ $binding->containerTag }} {{ $binding->directives }} {{ $attributes->only(['class', 'wire:key'])->class('ui-cascader ui-cascader-field')->merge(['data-size' => $size]) }}>
    @livewire(
        'ui::cascader',
        [
            ...$binding->parameters,
            'size' => $size,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
            'multiple' => $multiple,
            'selectParents' => $selectParents,
            'nodes' => $nodes,
            'placeholder' => $placeholder,
        ],
        key($binding->key))
    {{ $slot }}
    @isset($footer)
        <div class="ui-cascader-footer">{{ $footer }}</div>
    @endisset
</{{ $binding->containerTag }}>
