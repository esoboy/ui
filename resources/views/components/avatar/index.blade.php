@blaze
@props(['size' => 'default'])
<span {{ $attributes->class('ui-avatar group/avatar')->merge(['data-size' => $size]) }} x-data="{ loaded: false }">{{ $slot }}</span>
