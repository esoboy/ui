@blaze
@props(['size' => 'default', 'status' => null, 'variant' => 'default', 'link' => null])
@if($link !== null)<a href="{{ $link }}" {{ $attributes->class(['ui-badge group/badge', 'ui-badge-variant-'.$variant, \Esoboy\Ui\Support\ComponentStatus::cssClass($status)])->merge(['data-size' => $size]) }}>{{ $slot }}</a>@else<span {{ $attributes->class(['ui-badge group/badge', 'ui-badge-variant-'.$variant, \Esoboy\Ui\Support\ComponentStatus::cssClass($status)])->merge(['data-size' => $size]) }}>{{ $slot }}</span>@endif
