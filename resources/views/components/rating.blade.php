@props(['size' => 'default', 'max' => 5, 'value', 'initialValue'])
@php
    $binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, 'rating', get_defined_vars());
@endphp
@if ($binding->directives->isNotEmpty() || $attributes->has('wire:key'))<{{ $binding->containerTag }} {{ $binding->directives }} {{ $attributes->only('wire:key') }}>@endif
@livewire('ui::rating', [...$binding->parameters, 'max' => $max, 'size' => $size], key($binding->key))
@if ($binding->directives->isNotEmpty() || $attributes->has('wire:key'))</{{ $binding->containerTag }}>@endif
