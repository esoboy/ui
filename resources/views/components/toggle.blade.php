@blaze
@props(['pressed' => false, 'variant' => 'default', 'size' => 'default'])
@php
    if (!in_array($variant, ['default', 'outline'], true) || !in_array($size, ['sm', 'default', 'lg'], true)) throw new \InvalidArgumentException(__('ui::errors.toggle_style'));
@endphp
<label {{ $attributes->only('wire:key') }} class="ui-toggle ui-toggle-variant-{{ $variant }} {{ $attributes->get('class') }}" data-size="{{ $size }}">
    <input type="checkbox" {{ $attributes->except(['class', 'wire:key']) }} @checked($pressed) class="ui-visually-hidden">
    <span class="ui-toggle-control ui-toggle-control-variant-{{ $variant }}"
        data-size="{{ $size }}">{{ $slot }}</span>
</label>
