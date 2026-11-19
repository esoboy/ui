@blaze
@props(['variant' => 'legend'])
<legend {{ $attributes->merge(['data-slot' => 'field-legend', 'data-variant' => $variant]) }}>{{ $slot }}</legend>
