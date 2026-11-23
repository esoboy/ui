@blaze
@props(['orientation' => 'vertical', 'invalid' => false])
<div {{ $attributes->class('ui-field group/field')->merge(['data-orientation' => $orientation, 'data-invalid' => $invalid ? 'true' : null]) }}>{{ $slot }}</div>
