@blaze
@props(['variant' => 'default', 'size' => 'default', 'type' => 'button', 'link' => null, 'disabled' => false])
@if ($link !== null)
<a {{ $attributes->except(['href', 'type'])->class(['ui-button group/button', 'ui-button-size-'.$size, 'ui-button-variant-'.$variant])->merge(['href' => $disabled ? null : $link, 'role' => $disabled ? 'link' : null, 'aria-disabled' => $disabled ? 'true' : null, 'tabindex' => $disabled ? '-1' : null, 'data-slot' => 'button']) }}>{{ $slot }}</a>
@else
<button
    {{ $attributes->class(['ui-button group/button', 'ui-button-size-'.$size, 'ui-button-variant-'.$variant])->merge(['type' => $type, 'disabled' => $disabled, 'data-slot' => 'button']) }}>{{ $slot }}</button>
@endif
