@props(['inset' => false])
<div {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'menu-label', 'data-inset' => $inset ? '' : null]) }}>{{ $slot }}</div>
