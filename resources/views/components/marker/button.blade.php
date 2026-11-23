@props(['color' => null, 'variant' => 'default'])
<button {{ \Esoboy\Ui\Support\ComponentColor::attributes($color, 'marker', $attributes)->class('group/marker')->merge(['data-esoboy-ui' => '', 'data-slot' => 'marker', 'data-variant' => $variant, 'type' => 'button']) }}>{{ $slot }}</button>
