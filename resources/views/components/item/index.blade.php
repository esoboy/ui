@blaze
@props(['variant' => 'default', 'size' => 'default', 'link' => null])
@if($link !== null)<a href="{{ $link }}" {{ $attributes->class('group/item')->merge(['data-esoboy-ui' => '', 'data-slot' => 'item', 'data-variant' => $variant, 'data-size' => $size]) }}>{{ $slot }}</a>@else<div {{ $attributes->class('group/item')->merge(['data-esoboy-ui' => '', 'data-slot' => 'item', 'data-variant' => $variant, 'data-size' => $size]) }}>{{ $slot }}</div>@endif
