@props(['variant' => 'default'])
<span {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'code-block-line', 'data-variant' => $variant]) }}><span data-esoboy-ui data-slot="code-block-line-content">{{ $slot->isEmpty() ? "\n" : $slot }}</span></span>
