@blaze
@props(['status' => null, 'variant' => 'default', 'link' => null])
@if($link !== null)<a href="{{ $link }}" {{ $attributes->class(['ui-badge group/badge', 'ui-badge-variant-'.$variant, \Esoboy\Ui\Support\ComponentStatus::cssClass($status)]) }}>{{ $slot }}</a>@else<span {{ $attributes->class(['ui-badge group/badge', 'ui-badge-variant-'.$variant, \Esoboy\Ui\Support\ComponentStatus::cssClass($status)]) }}>{{ $slot }}</span>@endif
