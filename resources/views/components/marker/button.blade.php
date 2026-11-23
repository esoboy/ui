@props(['variant' => 'default'])
<button {{ $attributes->class('ui-marker-button group/marker')->merge(['class' => 'ui-marker ui-marker-variant-'.$variant, 'type' => 'button']) }}>{{ $slot }}</button>
