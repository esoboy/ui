@props(['target', 'index'])
<section {{ $attributes->merge(['data-esoboy-ui' => '', 'data-slot' => 'tour-step', 'data-target' => $target, 'data-index' => $index]) }}
    x-show="index === @js($index)">{{ $slot }}</section>
