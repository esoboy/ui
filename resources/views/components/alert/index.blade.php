@blaze
@props(['variant' => 'default'])
<div {{ $attributes->class('group/alert')->merge(['role' => 'alert', 'data-slot' => 'alert', 'data-variant' => $variant]) }}>
    {{ $slot }}</div>
