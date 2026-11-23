@blaze
@props(['status' => null, 'variant' => 'default', 'size' => 'default', 'type' => 'button', 'link' => null, 'disabled' => false])
@if ($link !== null)
<a {{ $attributes->except(['href', 'type'])->class(['ui-button group/button', 'ui-button-size-'.$size, 'ui-button-variant-'.$variant, \Esoboy\Ui\Support\ComponentStatus::cssClass($status)])->merge(['href' => $disabled ? null : $link, 'role' => $disabled ? 'link' : null, 'aria-disabled' => $disabled ? 'true' : null, 'tabindex' => $disabled ? '-1' : null]) }}>{{ $slot }}</a>
@else
<button
    {{ $attributes->class(['ui-button group/button', 'ui-button-size-'.$size, 'ui-button-variant-'.$variant, \Esoboy\Ui\Support\ComponentStatus::cssClass($status)])->merge(['type' => $type, 'disabled' => $disabled]) }}>{{ $slot }}</button>
@endif
