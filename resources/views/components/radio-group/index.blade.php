@props(['color' => null])
<div role="radiogroup" {{ \Esoboy\Ui\Support\ComponentColor::attributes($color, 'radio', $attributes)->merge(['data-esoboy-ui' => '', 'data-slot' => 'radio-group']) }}>{{ $slot }}</div>
