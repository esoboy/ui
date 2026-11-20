@blaze
@props(['size' => 'default'])
<section {{ $attributes->class('group/card')->merge(['data-esoboy-ui' => '', 'data-slot' => 'card', 'data-size' => $size]) }}>{{ $slot }}</section>
