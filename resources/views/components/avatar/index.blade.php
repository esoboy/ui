@blaze
@props(['size' => 'default'])
<span {{ $attributes->class('group/avatar')->merge(['data-esoboy-ui' => '', 'data-slot' => 'avatar', 'data-size' => $size]) }} x-data="{ loaded: false }">{{ $slot }}</span>
