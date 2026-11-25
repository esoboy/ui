@props(['size' => 'default', 'value', 'initialValue', 'min' => 0, 'max' => 100, 'step' => 1, 'orientation' => 'horizontal', 'inverted' => false])
@php
    $binding = \Esoboy\Ui\Support\NativeControl::from($attributes, 'slider', get_defined_vars(), ['min' => $min, 'max' => $max, 'step' => $step, 'orientation' => $orientation, 'inverted' => $inverted]);
    $config = $binding->config;
@endphp
<{{ $binding->containerTag }} {{ $binding->loading }} {{ $attributes->only(['class', 'wire:key'])->class('ui-slider ui-slider-field')->merge(['data-size' => $size]) }}>
    @if ($config['mode'] === 'internal')
        @livewire('ui::slider', ['defaultValue' => $config['value'], 'settings' => \Illuminate\Support\Arr::except($config, 'value'), 'fieldAttributes' => $attributes->except(['class', 'wire:key'])->all()], key($binding->key))
    @else
        @include('ui::partials.slider', ['inputId' => $binding->id, 'controlKey' => $binding->key])
    @endif
    {{ $slot }}
</{{ $binding->containerTag }}>
