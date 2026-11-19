@blaze
@props(['size' => 'default'])
<div {{ $attributes->merge(['data-slot' => 'empty', 'data-size' => $size]) }}>{{ $slot }}</div>
