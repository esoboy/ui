@props(['inset' => false])
<div {{ $attributes->merge(['data-slot' => 'menu-label', 'data-inset' => $inset ? '' : null]) }}>{{ $slot }}</div>
