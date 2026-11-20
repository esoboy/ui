@blaze
@props(['orientation' => 'vertical', 'invalid' => false])
<div {{ $attributes->class('group/field')->merge(['data-esoboy-ui' => '', 'data-slot' => 'field', 'data-orientation' => $orientation, 'data-invalid' => $invalid ? 'true' : null]) }}>{{ $slot }}</div>
