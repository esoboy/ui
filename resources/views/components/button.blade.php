@blaze
@props(['color' => null, 'variant' => 'default', 'size' => 'default', 'type' => 'button', 'link' => null, 'disabled' => false])
@php
    $attributes = \Esoboy\Ui\Support\ComponentColor::attributes($color, 'button', $attributes);
@endphp
@if ($link !== null)
<a {{ $attributes->except(['href', 'type'])->class(['ui-button group/button', 'ui-button-size-'.$size, 'ui-button-variant-'.$variant])->merge(['href' => $disabled ? null : $link, 'role' => $disabled ? 'link' : null, 'aria-disabled' => $disabled ? 'true' : null, 'tabindex' => $disabled ? '-1' : null, 'data-esoboy-ui' => '', 'data-slot' => 'button']) }}>{{ $slot }}</a>
@else
<button
    {{ $attributes->class(['ui-button group/button', 'ui-button-size-'.$size, 'ui-button-variant-'.$variant])->merge(['type' => $type, 'disabled' => $disabled, 'data-esoboy-ui' => '', 'data-slot' => 'button']) }}>{{ $slot }}</button>
@endif
