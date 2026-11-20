@props(['variant' => 'default'])
<button {{ $attributes->class('group/marker')->merge(['data-esoboy-ui' => '', 'data-slot' => 'marker', 'data-variant' => $variant, 'type' => 'button']) }}>{{ $slot }}</button>
