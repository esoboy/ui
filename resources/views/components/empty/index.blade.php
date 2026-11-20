@blaze
@props(['size' => 'default'])
<div {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'empty', 'data-size' => $size]) }}>{{ $slot }}</div>
