@blaze
@props(['status' => null, 'variant' => 'default', 'size' => 'default'])
<span {{ $attributes->class(['ui-button ui-button-text group/button', 'ui-button-size-'.$size, 'ui-button-variant-'.$variant, \Esoboy\Ui\Support\ComponentStatus::cssClass($status)]) }}>{{ $slot }}</span>
