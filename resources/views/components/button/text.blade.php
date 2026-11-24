@blaze
@props(['status' => null, 'variant' => 'default', 'size' => null])
@php($size ??= \Esoboy\Ui\View\ButtonGroup::defaultSize())
<span {{ $attributes->class(['ui-button ui-button-text group/button', 'ui-button-size-'.$size, 'ui-button-variant-'.$variant, \Esoboy\Ui\Support\ComponentStatus::cssClass($status)]) }}>{{ $slot }}</span>
