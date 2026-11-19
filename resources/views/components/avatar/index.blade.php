@blaze
@props(['size' => 'default'])
<span {{ $attributes->class('group/avatar')->merge(['data-slot' => 'avatar', 'data-size' => $size]) }} x-data="{ loaded: false }">{{ $slot }}</span>
