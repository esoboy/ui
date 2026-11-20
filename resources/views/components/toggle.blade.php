@blaze
@props(['pressed' => false, 'variant' => 'default', 'size' => 'default'])
@php
    if (!in_array($variant, ['default', 'outline'], true) || !in_array($size, ['sm', 'default', 'lg'], true)) throw new \InvalidArgumentException(__('ui::errors.toggle_style'));
@endphp
<label data-esoboy-ui data-slot="toggle" data-variant="{{ $variant }}" data-size="{{ $size }}">
    <input type="checkbox" {{ $attributes->except('class') }} @checked($pressed) class="ui-visually-hidden">
    <span class="ui-toggle {{ $attributes->get('class') }}" data-variant="{{ $variant }}"
        data-size="{{ $size }}">{{ $slot }}</span>
</label>
