@blaze
@props(['variant' => 'default', 'link' => null])
@if($link !== null)<a href="{{ $link }}" {{ $attributes->class('group/marker')->merge(['data-slot' => 'marker', 'data-variant' => $variant]) }}>{{ $slot }}</a>@else<div {{ $attributes->class('group/marker')->merge(['data-slot' => 'marker', 'data-variant' => $variant]) }}>{{ $slot }}</div>@endif
