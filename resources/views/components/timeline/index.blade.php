@blaze
@props(['color' => null])
<ol {{ \Esoboy\Ui\Support\ComponentColor::attributes($color, 'timeline', $attributes)->merge(['data-esoboy-ui' => '', 'data-slot' => 'timeline']) }}>{{ $slot }}</ol>
