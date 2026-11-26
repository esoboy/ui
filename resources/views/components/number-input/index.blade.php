@props(['size' => 'default', 'value', 'initialValue', 'min' => null, 'max' => null, 'step' => 1])
@php
    $binding = \Esoboy\Ui\Support\NativeControl::from($attributes, 'number-input', get_defined_vars(), ['min' => $min, 'max' => $max, 'step' => $step]);
    $config = $binding->config;
    $directives = $attributes->whereStartsWith('wire:')->whereDoesntStartWith(['wire:model', 'wire:blur', 'wire:focus', 'wire:change'])->except('wire:key');
@endphp
<{{ $binding->containerTag }} {{ $directives }} {{ $attributes->only(['class', 'wire:key'])->class('ui-number-input ui-number-input-field') }}>
    @if ($config['mode'] === 'controlled')
        @livewire('ui::number-input', [
            'mode' => 'controlled', 'externalValue' => $config['value'], 'disabled' => $config['disabled'], 'readonly' => $config['readonly'],
            'settings' => compact('min', 'max', 'step', 'size'), 'applicationLocale' => app()->getLocale(),
            'fieldAttributes' => $attributes->whereDoesntStartWith('wire:')->except('class')->all(),
        ], key($binding->key))
    @else
        @include('ui::partials.number-input', [...$config, 'numberValue' => $config['value'], 'id' => $binding->id, 'controlKey' => $binding->key])
    @endif
    {{ $slot }}
    @isset($footer)
        <div class="ui-number-input-footer">{{ $footer }}</div>
    @endisset
</{{ $binding->containerTag }}>
