@blaze

@props([
    'variant' => 'default',
    'size' => 'default',
    'defaultPressed' => false,
])

{{-- A native checkbox, so wire:model binds as usual; class styles the toggle itself. --}}
<label {{ $attributes->only(['class', 'style'])->merge(['data-slot' => 'toggle', 'data-variant' => $variant, 'data-size' => $size]) }}>
    <input {{ $attributes->except(['class', 'style'])->merge(['type' => 'checkbox', 'checked' => (bool) $defaultPressed]) }}>
    {{ $slot }}
</label>
