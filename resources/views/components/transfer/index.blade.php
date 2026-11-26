@props(['size' => 'default', 'value', 'initialValue', 'options' => [], 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'transfer', get_defined_vars()))
<{{ $binding->containerTag }} {{ $binding->directives }} {{ $attributes->only(['class', 'wire:key'])->class('ui-transfer ui-transfer-field')->merge(['data-size' => $size]) }}>
    @livewire(
        'ui::transfer',
        [
            ...$binding->parameters,
            'size' => $size,
            'options' => $options,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
        ],
        key($binding->key))
    {{ $slot }}
    @isset($footer)
        <div class="ui-transfer-footer">{{ $footer }}</div>
    @endisset
</{{ $binding->containerTag }}>
