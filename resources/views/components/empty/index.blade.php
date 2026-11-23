@blaze
@props(['size' => 'default'])
<div {{ $attributes->merge(['class' => 'ui-empty', 'data-size' => $size]) }}>{{ $slot }}</div>
