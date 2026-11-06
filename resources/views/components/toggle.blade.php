@blaze

@props([
    'variant' => 'default',
    'size' => 'default',
    'checked' => false,
])

@php($attributes = \Esoboy\Ui\Support\Theme::control($attributes))

{{-- A native checkbox, so wire:model binds as usual; class styles the toggle itself. --}}
<label {{ $attributes->only(['class', 'style'])->merge(['data-slot' => 'toggle', 'data-variant' => $variant, 'data-size' => $size]) }}>
    <input {{ $attributes->except(['class', 'style'])->merge(['type' => 'checkbox', 'checked' => \Esoboy\Ui\Support\Model::checked($attributes, (bool) $checked)]) }}>
    {{ $slot }}
</label>
