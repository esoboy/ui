@blaze
@props(['color' => null, 'variant' => 'default', 'link' => null])
@php
    $attributes = \Esoboy\Ui\Support\ComponentColor::attributes($color, 'marker', $attributes);
@endphp
@if($link !== null)<a href="{{ $link }}" {{ $attributes->class('group/marker')->merge(['data-esoboy-ui' => '', 'data-slot' => 'marker', 'data-variant' => $variant]) }}>{{ $slot }}</a>@else<div {{ $attributes->class('group/marker')->merge(['data-esoboy-ui' => '', 'data-slot' => 'marker', 'data-variant' => $variant]) }}>{{ $slot }}</div>@endif
