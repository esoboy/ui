@blaze
@props(['size' => 'default'])
<section {{ $attributes->class('ui-card group/card')->merge(['data-size' => $size]) }}>{{ $slot }}</section>
