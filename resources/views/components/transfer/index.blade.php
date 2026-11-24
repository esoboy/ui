@props(['size' => 'default', 'value', 'initialValue', 'options' => [], 'emptyTitle' => null, 'emptyDescription' => null])
@php($binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes->except('class'), 'transfer', get_defined_vars()))
<div {{ $attributes->only('class')->class('ui-transfer ui-transfer-field')->merge(['data-size' => $size]) }}>
    {!! \Livewire\Livewire::mount(
        'ui::transfer',
        [
            ...$binding->parameters,
            'size' => $size,
            'options' => $options,
            'emptyTitle' => $emptyTitle,
            'emptyDescription' => $emptyDescription,
        ],
        $binding->key,
    ) !!}
    {{ $slot }}
    @isset($footer)
        <div class="ui-transfer-footer">{{ $footer }}</div>
    @endisset
</div>
