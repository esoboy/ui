@blaze
@props(['color' => null])
<li {{ \Esoboy\Ui\Support\ComponentColor::attributes($color, 'timeline', $attributes)->merge(['data-esoboy-ui' => '', 'data-slot' => 'timeline-item']) }}>{{ $slot }}</li>
