@blaze
@props(['color' => null, 'variant' => 'default', 'link' => null])
@php
    $attributes = \Esoboy\Ui\Support\ComponentColor::attributes($color, 'badge', $attributes);
@endphp
@if($link !== null)<a href="{{ $link }}" {{ $attributes->class('group/badge')->merge(['data-esoboy-ui' => '', 'data-slot' => 'badge', 'data-variant' => $variant]) }}>{{ $slot }}</a>@else<span {{ $attributes->class('group/badge')->merge(['data-esoboy-ui' => '', 'data-slot' => 'badge', 'data-variant' => $variant]) }}>{{ $slot }}</span>@endif
