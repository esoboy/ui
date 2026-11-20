@blaze
@props(['variant' => 'default'])
<div {{ $attributes->class('group/alert')->merge(['role' => 'alert', 'data-esoboy-ui' => '', 'data-slot' => 'alert', 'data-variant' => $variant]) }}>
    {{ $slot }}</div>
