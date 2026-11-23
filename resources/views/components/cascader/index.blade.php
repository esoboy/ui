@props(['value', 'initialValue', 'multiple' => false, 'selectParents' => false, 'nodes' => [], 'placeholder' => '', 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'cascader', get_defined_vars()))
<div {{ $attributes->only('class')->class('ui-cascader ui-cascader-field') }}>
    {!! \Livewire\Livewire::mount(
        'ui::cascader',
        [
            ...$binding->parameters,
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
        <div class="ui-cascader-footer">{{ $footer }}</div>
    @endisset
</div>
