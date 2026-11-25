@blaze
@props(['size' => 'default'])
<span {{ $attributes->class('ui-avatar group/avatar')->merge(['data-size' => $size]) }}>{{ $slot }}</span>
