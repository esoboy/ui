@blaze
@props(['size' => 'default'])
<section {{ $attributes->class('group/card')->merge(['data-slot' => 'card', 'data-size' => $size]) }}>{{ $slot }}</section>
