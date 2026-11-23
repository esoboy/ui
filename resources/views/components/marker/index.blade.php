@blaze
@props(['variant' => 'default', 'link' => null])
@if($link !== null)<a href="{{ $link }}" {{ $attributes->class(['group/marker ui-marker', 'ui-marker-variant-'.$variant]) }}>{{ $slot }}</a>@else<div {{ $attributes->class(['group/marker ui-marker', 'ui-marker-variant-'.$variant]) }}>{{ $slot }}</div>@endif
