@props(['variant' => 'default'])
<button {{ $attributes->class('group/marker')->merge(['data-slot' => 'marker', 'data-variant' => $variant, 'type' => 'button']) }}>{{ $slot }}</button>
