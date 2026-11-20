@blaze
@props(['variant' => 'legend'])
<legend {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'field-legend', 'data-variant' => $variant]) }}>{{ $slot }}</legend>
