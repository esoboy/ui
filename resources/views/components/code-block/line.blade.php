@props(['variant' => 'default'])
<span {{ $attributes->merge(['data-slot' => 'code-block-line', 'data-variant' => $variant]) }}><span data-slot="code-block-line-content">{{ $slot->isEmpty() ? "\n" : $slot }}</span></span>
