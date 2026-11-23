@blaze
@props(['variant' => 'legend'])
<legend {{ $attributes->class('ui-field-legend ui-field-legend-variant-'.$variant) }}>{{ $slot }}</legend>
