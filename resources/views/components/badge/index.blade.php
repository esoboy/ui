@blaze
@props(['variant' => 'default', 'link' => null])
@if($link !== null)<a href="{{ $link }}" {{ $attributes->class('group/badge')->merge(['data-esoboy-ui' => '', 'data-slot' => 'badge', 'data-variant' => $variant]) }}>{{ $slot }}</a>@else<span {{ $attributes->class('group/badge')->merge(['data-esoboy-ui' => '', 'data-slot' => 'badge', 'data-variant' => $variant]) }}>{{ $slot }}</span>@endif
