@php
    $binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, $kind);
    $parameters = [...$binding->parameters, 'kind' => $kind, 'settings' => $settings, 'initialValue' => $initial];
    $__slots = ['default' => $slot->toHtml()];
@endphp
@if ($binding->directives->isNotEmpty() || $attributes->has('wire:key'))
    <{{ $binding->containerTag }} {{ $binding->directives }} {{ $attributes->only('wire:key') }}>
        @livewire('ui::panel-state', $parameters, key($binding->key), $__slots ?? [])
    </{{ $binding->containerTag }}>
@else
    @livewire('ui::panel-state', $parameters, key($binding->key), $__slots ?? [])
@endif
