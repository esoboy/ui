@props(['inset' => false])
<div {{ $attributes->merge(['class' => 'ui-menu-label', 'data-inset' => $inset ? '' : null]) }}>{{ $slot }}</div>
