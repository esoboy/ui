@props(['size' => 'default', 'max' => 5, 'value', 'initialValue'])
@php
    $binding = \Esoboy\Ui\Support\ControlAttributes::from($attributes, 'rating', get_defined_vars());
@endphp
@unless ($binding->loading->isEmpty())<fieldset {{ $binding->loading }} {{ $attributes->only('wire:key') }}>@endunless
@livewire('ui::rating', [...$binding->parameters, 'max' => $max, 'size' => $size], key($binding->key))
@unless ($binding->loading->isEmpty())</fieldset>@endunless
