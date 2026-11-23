@blaze
@props(['color' => null])
<label {{ \Esoboy\Ui\Support\ComponentColor::attributes($color, 'ink', $attributes)->class('ui-label')->merge(['data-esoboy-ui' => '', 'data-slot' => 'label']) }}>{{ $slot }}</label>
